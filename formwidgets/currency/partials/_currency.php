<?php if ($this->previewMode): ?>
    <div class="form-control">
        <?= e(Currency::format($value, ['baseValue' => false, 'format' => $format])) ?>
    </div>
<?php else: ?>
    <div
        id="<?= $this->getId() ?>"
        class="input-group">
        <?php if ($symbolBefore): ?>
            <span
                class="input-group-addon input-group-text"
                <?php if ($currencyCode): ?>data-tooltip-text="<?= e($currencyCode) ?>"<?php endif ?>
            ><?= e($symbol) ?></span>
        <?php endif ?>
        <input
            name="<?= $name ?>"
            id="<?= $this->getId('textarea') ?>"
            value="<?= e($value) ?>"
            placeholder="<?= e(trans($field->placeholder)) ?>"
            class="form-control"
            autocomplete="off"
            <?= $field->getAttributes() ?>
            />
        <?php if (!$symbolBefore): ?>
            <span class="input-group-addon input-group-text"><?= e($symbol) ?></span>
        <?php endif ?>
    </div>
<?php endif ?>
