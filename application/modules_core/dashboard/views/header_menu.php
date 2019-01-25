<?php foreach ($menu_items as $item) { ?>

<li <?php if (isset($item['class'])) { ?>class="<?php echo $item['class']; ?>"<?php } ?>>
    <?php echo anchor($item['href'], $this->lang->line($item['title'])); ?>
    <?php if (isset($item['submenu'])) { ?>
    <ul>
        <?php foreach ($item['submenu'] as $subitem) { ?>
        <li><?php echo anchor($subitem['href'], $this->lang->line($subitem['title'])); ?></li>
        <?php } ?>
    </ul>
    <?php } ?>
</li>

<?php } ?>
