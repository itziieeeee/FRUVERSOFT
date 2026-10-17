<?php

namespace App\Controllers;

use App\Models\ProductoModel;

class Catalogo extends BaseController
{
    private $session;
    private $productoModel;

    // Clave de la sesión donde vive el carrito (equivale a 'libro_carrito' del manual)
    private const CLAVE = 'carrito_pedido';

    public function __construct()
    {
        $this->session       = session();
        $this->productoModel = new ProductoModel();
    }


    /**
     * Producto + existencias (e_total) + precio y unidad de la entrada más reciente.
     * Las columnas e_total / precio / unidad_venta NO están en la tabla producto.
     */
    private function consultaProductos()
    {
        return $this->productoModel
            ->select("producto.*,
                COALESCE(existencias.e_total, 0) AS e_total,
                COALESCE(existencias.e_bloqueo, 0) AS e_bloqueo,
                (COALESCE(existencias.e_total, 0) - COALESCE(existencias.e_bloqueo, 0)) AS stock_disponible,
                (SELECT e.precio_sugerido FROM entrada e WHERE e.id_producto = producto.id ORDER BY e.fecha DESC, e.id DESC LIMIT 1) AS precio,
                (SELECT e.unidad_venta FROM entrada e WHERE e.id_producto = producto.id ORDER BY e.fecha DESC, e.id DESC LIMIT 1) AS unidad_venta", false)
            ->join('existencias', 'existencias.id_producto = producto.id', 'left');
    }

    private function buscarProducto($id)
    {
        return $this->consultaProductos()->where('producto.id', $id)->first();
    }

    /** Catálogo con búsqueda y paginación (cliente y vendedor) */
    public function index()
    {
        $q = $this->request->getGet('q');

        $this->consultaProductos();

        if (!empty($q)) {
            $this->productoModel->groupStart()
                ->like('producto.nombre', $q)
                ->orLike('producto.descripcion', $q)
                ->groupEnd();
        }

        $productos = $this->productoModel->paginate(8);
        $carrito   = $this->session->get(self::CLAVE) ?? [];

        foreach ($productos as &$p) {
            $enCarrito         = (float) ($carrito[$p['id']]['cantidad'] ?? 0);
            $p['en_carrito']   = $enCarrito;
            // disponible = (e_total - e_bloqueo) - lo que ya está en el carrito
            $p['disponible']   = max(0, (float) $p['stock_disponible'] - $enCarrito);
        }
        unset($p);

        $data = [
            'productos'    => $productos,
            'pager'        => $this->productoModel->pager,
            'q'            => $q,
            'totalItems'   => count($this->session->get(self::CLAVE) ?? []),
            'urlRegresar'  => $this->urlRegresar(),
        ];

        return view('catalogo_cliente', $data);
    }

    /** Agrega un producto al carrito validando existencias */
    public function agregar()
    {
        $id       = (int) $this->request->getPost('id');
        $cantidad = (float) $this->request->getPost('cantidad');

        $producto = $this->buscarProducto($id);

        if (!$producto) {
            return redirect()->back()->with('error', 'El producto no existe.');
        }

        $stock = (float) ($producto['stock_disponible'] ?? 0);

        if ($cantidad <= 0 || $cantidad > $stock) {
            return redirect()->back()->with('error', "Cantidad no disponible (máximo: $stock).");
        }

        $carrito = $this->session->get(self::CLAVE) ?? [];

        if (isset($carrito[$id])) {
            $nueva = $carrito[$id]['cantidad'] + $cantidad;
            if ($nueva > $stock) {
                return redirect()->back()->with('error', 'No puedes agregar más: supera las existencias.');
            }
            $carrito[$id]['cantidad'] = $nueva;
            $carrito[$id]['subtotal'] = $nueva * $carrito[$id]['precio'];
        } else {
            $precio = (float) ($producto['precio'] ?? 0);
            $carrito[$id] = [
                'id'       => $producto['id'],
                'nombre'   => $producto['nombre'],
                'imagen'   => $producto['imagen'],
                'unidad'   => $producto['unidad_venta'] ?? 'pieza',
                'precio'   => $precio,
                'cantidad' => $cantidad,
                'subtotal' => $cantidad * $precio,
            ];
        }

        $this->session->set(self::CLAVE, $carrito);

        return redirect()->back()->with('mensaje', '"' . $producto['nombre'] . '" agregado al carrito.');
    }

    /** Muestra el carrito y el total */
    public function ver()
    {
        $carrito = $this->session->get(self::CLAVE) ?? [];
        $total   = array_sum(array_column($carrito, 'subtotal'));

        return view('carrito_cliente', [
            'carrito' => $carrito,
            'total'   => $total,
        ]);
    }

    /** Cambia la cantidad de un renglón */
    public function actualizar($id)
    {
        $cantidad = (float) $this->request->getPost('cantidad');
        $carrito  = $this->session->get(self::CLAVE) ?? [];

        if (!isset($carrito[$id])) {
            return redirect()->to(base_url('carrito'));
        }

        $producto = $this->buscarProducto($id);
        $stock    = (float) ($producto['stock_disponible'] ?? 0);

        if ($cantidad <= 0) {
            unset($carrito[$id]);
        } elseif ($cantidad > $stock) {
            return redirect()->to(base_url('carrito'))->with('error', "Solo hay $stock disponibles.");
        } else {
            $carrito[$id]['cantidad'] = $cantidad;
            $carrito[$id]['subtotal'] = $cantidad * $carrito[$id]['precio'];
        }

        $this->session->set(self::CLAVE, $carrito);
        return redirect()->to(base_url('carrito'))->with('mensaje', 'Carrito actualizado.');
    }

    public function quitar($id)
    {
        $carrito = $this->session->get(self::CLAVE) ?? [];
        unset($carrito[$id]);
        $this->session->set(self::CLAVE, $carrito);

        return redirect()->to(base_url('carrito'))->with('mensaje', 'Producto quitado.');
    }

    public function vaciar()
    {
        $this->session->remove(self::CLAVE);
        return redirect()->to(base_url('carrito'))->with('mensaje', 'Carrito vaciado.');
    }

    /**
     * Botón "Regresar": ajusta la condición a como guardes la sesión del vendedor.
     */
    private function urlRegresar(): string
    {
        return $this->session->get('usuario') ? base_url('menusolo') : base_url('/');
    }
}