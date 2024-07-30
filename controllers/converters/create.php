<?php Block::put('breadcrumb') ?>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= Backend::url('responsiv/currency/rates') ?>">Rates</a></li>
        <li class="breadcrumb-item"><a href="<?= Backend::url('responsiv/currency/converters') ?>">Converters</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= e($this->pageTitle) ?></li>
    </ol>
<?php Block::endPut() ?>

<?php if (!$this->fatalError): ?>
    <?= Form::open(['class' => 'layout design-basic']) ?>

        <input type="hidden" name="driver_alias" value="<?= $this->driverAlias ?>" />

        <div class="scoreboard">
            <div data-control="toolbar">
                <div class="scoreboard-item title-value">
                    <h4><?= __("Currency Converter") ?></h4>
                    <p class="oc-icon-credit-card"><?= $formModel->converter_name ?></p>
                </div>
            </div>
        </div>

        <div class="layout-row">
            <?= $this->formRender() ?>
        </div>

        <div class="form-buttons pt-3">
            <div data-control="loader-container">
                <?= $this->formRender(['section' => 'buttons']) ?>
            </div>
        </div>

    <?= Form::close() ?>
<?php else: ?>
    <?= $this->formRenderDesign() ?>
<?php endif ?>
