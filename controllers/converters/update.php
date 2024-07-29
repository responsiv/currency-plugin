<?php Block::put('breadcrumb') ?>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= Backend::url('responsiv/currency/rates') ?>">Rates</a></li>
        <li class="breadcrumb-item"><a href="<?= Backend::url('responsiv/currency/converters') ?>">Converters</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= e($this->pageTitle) ?></li>
    </ol>
<?php Block::endPut() ?>

<?= $this->formRenderDesign() ?>
