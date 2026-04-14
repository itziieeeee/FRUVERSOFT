<?php $pager->setSurroundCount(2); ?>

<nav aria-label="Barra de Navegacion">
<ul class="custom-pagination">

    <?php if ($pager->hasPrevious()): ?>
        <li>
            <a href="<?= $pager->getFirst() ?>">« Primero</a></li>
        <li>
            <a href="<?= $pager->getPrevious() ?>">‹ Anterior</a>
        </li>
    <?php endif; ?>
  <?php foreach ($pager->links() as $link): ?>
        <li class="<?= $link['active'] ? 'active' : '' ?>">
            <a href="<?= $link['uri'] ?>">
                <?= $link['title'] ?>
            </a>
        </li>
    <?php endforeach; ?>
    <?php if ($pager->hasNext()): ?>
        <li>
            <a href="<?= $pager->getNext() ?>">Siguiente ›</a>
        </li>
        <li>
            <a href="<?= $pager->getLast() ?>">Último »</a>
        </li>
    <?php endif; ?>

</ul>
</nav>