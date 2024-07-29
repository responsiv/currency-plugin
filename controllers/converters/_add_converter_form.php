<?= Form::open(['id' => 'addGatewayForm']) ?>
    <div class="modal-header">
        <h4 class="modal-title"><?= __("Add Currency Converter") ?></h4>
        <button type="button" class="btn-close" data-dismiss="popup"></button>
    </div>
    <div class="modal-body">
        <?php if ($this->fatalError): ?>
            <p class="flash-message static error"><?= $fatalError ?></p>
        <?php else: ?>
            <div class="control-simplelist is-selectable is-scrollable size-large" data-control="simplelist">
                <ul>
                    <?php foreach ($converters as $converter): ?>
                        <li>
                            <a href="<?= Backend::url('responsiv/currency/converters/create/' . $converter->alias) ?>">
                                <h5 class="heading"><?= $converter->name ?></h5>
                                <p class="description"><?= $converter->description ?></p>
                            </a>
                        </li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif ?>
    </div>
    <div class="modal-footer">
        <?= Ui::button(__("Close"))->secondary()->dismissPopup() ?>
    </div>
<?= Form::close() ?>
