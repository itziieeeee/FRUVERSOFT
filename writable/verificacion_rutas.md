# Verificación del motor de rutas FRUVERSOFT
Generado: 2026-11-25 06:08 (semilla fija 2026, se puede repetir con `php spark rutas:verificar`).

## 1. Dijkstra contra métodos independientes

**Grafo real** (tablas `ruta_nodo` y `ruta_arista`, solo los registros activos): 16 nodos, 30 conexiones dirigidas.
- [OK] Dijkstra coincide con Floyd-Warshall en los 256 pares de nodos del grafo real (0 sin camino, devueltos como null).
- [OK] Dijkstra coincide con la búsqueda exhaustiva (todos los caminos posibles) en 200 grafos aleatorios de 4 a 8 nodos (7561 pares comparados).
- [OK] Caso a mano: de 1 a 2 hay una arista de 10 km, pero 1→3→2 cuesta 3+4 = 7 km; Dijkstra devuelve 7 km.
- [OK] Caso a mano: el nodo 4 está aislado y Dijkstra devuelve null (sin ruta disponible).

## 2. Árbol de expansión mínima (Prim)

- [OK] En 225 casos aleatorios (2 a 10 puntos) el árbol tiene exactamente N-1 aristas, une todos los puntos en un solo grupo y no tiene ciclos.
- [OK] El costo total del árbol de Prim es igual al de Kruskal (algoritmo independiente) en todos los casos.
- [OK] Con 2 puntos el árbol tiene 1 arista.
- [OK] Con 1 punto el árbol no tiene aristas.

## 3. El orden elegido es el de menor costo

- [OK] En 120 casos (2 a 7 puntos) el orden elegido cuesta como máximo 0.390 km más que el mínimo absoluto (el sistema tolera 0.5 km para empezar por la parada más cercana).
- [OK] En 100 casos de 6 paradas el orden optimizado nunca es más largo que el orden de captura (100 de 100 igual o mejor).

## 4. Tiempo de cálculo según el número de puntos

Se mide el cálculo del mejor orden (sin consultar OSRM), promedio de 5 repeticiones (2 con 8 puntos). La memoria es el pico acumulado del proceso.

| Puntos | Combinaciones evaluadas | Método | Tiempo promedio | Memoria máx. |
|---|---|---|---|---|
| 2 | 2 | Todas las combinaciones | 0.11 ms | 14.0 MB |
| 4 | 24 | Todas las combinaciones | 0.99 ms | 14.0 MB |
| 6 | 720 | Todas las combinaciones | 29.25 ms | 14.0 MB |
| 7 | 5,040 | Todas las combinaciones | 230.96 ms | 14.0 MB |
| 8 | 40,320 | Todas las combinaciones | 2.06 s | 34.0 MB |
| 10 | 10 (vecino más cercano) | Vecino más cercano (más de 8) | 0.27 ms | 34.0 MB |
| 15 | 15 (vecino más cercano) | Vecino más cercano (más de 8) | 0.56 ms | 34.0 MB |

Con más de 8 puntos el sistema cambia solo al método del vecino más cercano para no tardar. Además, el máximo configurado por salida es de 6 pedidos.

## 5. Rutas con direcciones reales de la base de datos

Local: 19.220676, -97.778467. Distancias por carretera de OSRM cuando responde.

| Ruta | Paradas | Orden de captura (km) | Orden optimizado (km) | Ahorro | Línea recta (km) | Fuente | Google Maps (km) | Diferencia |
|---|---|---|---|---|---|---|---|---|
| Ruta A (Tlaxcala oriente) | 3 | 92.58 | 80.91 | 11.67 km (12.6%) | 49.72 | osrm | ______ | ______ |
| Ruta B (Puebla sur y capital) | 3 | 134.09 | 134.09 | 0.00 km (0.0%) | 95.56 | osrm | ______ | ______ |
| Ruta C (las 5 direcciones) | 5 | 191.48 | 163.76 | 27.72 km (14.5%) | 120.36 | osrm | ______ | ______ |

Orden optimizado de cada ruta (para capturarlo igual en Google Maps):

- **Ruta A (Tlaxcala oriente):** Local → dirección 4 (19.25498, -97.89146) → dirección 1 (19.34883, -97.96516) → dirección 8 (19.32305, -97.91810) → Local
- **Ruta B (Puebla sur y capital):** Local → dirección 2 (19.06469, -97.93483) → dirección 3 (19.07095, -98.16363) → dirección 4 (19.25498, -97.89146) → Local
- **Ruta C (las 5 direcciones):** Local → dirección 2 (19.06469, -97.93483) → dirección 3 (19.07095, -98.16363) → dirección 4 (19.25498, -97.89146) → dirección 1 (19.34883, -97.96516) → dirección 8 (19.32305, -97.91810) → Local

Para llenar las dos últimas columnas: en Google Maps captura el mismo orden (origen y destino = local, paradas intermedias en el orden indicado), en modo auto, y anota los km totales.

## Resultado general
TODAS LAS PRUEBAS PASARON.
