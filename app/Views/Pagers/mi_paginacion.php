<?php $pager->setSurroundCount(2); ?>

<nav aria-label="Barra de Navegacion">
<ul class="custom-pagination">

    <?php if ($pager->hasPrevious()): ?>
        <li>
            <a href="<?= $pager->getFirst() ?>" title="Primera página">
                <i class="fas fa-angle-double-left"></i>
            </a>
        </li>
        <li>
            <a href="<?= $pager->getPrevious() ?>" title="Página anterior">
                <i class="fas fa-angle-left"></i>
            </a>
        </li>
    <?php endif; ?>

    <?php foreach ($pager->links() as $link): ?>
        <?php if ($link['active']): ?>
            <li class="active">
                <a href="<?= $link['uri'] ?>"><?= $link['title'] ?></a>
            </li>
        <?php elseif (is_numeric($link['title'])): ?>
            <li>
                <a href="<?= $link['uri'] ?>"><?= $link['title'] ?></a>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php if ($pager->hasNext()): ?>
        <li>
            <a href="<?= $pager->getNext() ?>" title="Página siguiente">
                <i class="fas fa-angle-right"></i>
            </a>
        </li>
        <li>
            <a href="<?= $pager->getLast() ?>" title="Última página">
                <i class="fas fa-angle-double-right"></i>
            </a>
        </li>
    <?php endif; ?>

</ul>
</nav>