<?php Block::put('breadcrumb') ?>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= Backend::url('system/settings') ?>"><?= __("Settings") ?></a></li>
        <li class="breadcrumb-item"><a href="<?= Backend::url('responsiv/currency/rates') ?>"><?= __("Manage Rates") ?></a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= e($this->pageTitle) ?></li>
    </ol>
<?php Block::endPut() ?>


<?= $this->listRender() ?>
