<div data-control="toolbar loader-container">
    <?= Ui::button("New Rate", 'responsiv/currency/rates/create')
        ->icon('icon-plus')
        ->primary() ?>

    <?= Ui::ajaxButton("Delete", 'onDeleteSelected')
        ->listCheckedTrigger()
        ->listCheckedRequest()
        ->icon('icon-delete')
        ->secondary()
        ->confirmMessage("Are you sure?") ?>


    <div class="toolbar-divider"></div>

    <?= Ui::button("Currency Converters", 'responsiv/currency/converters')
        ->icon('icon-line-chart')
        ->secondary() ?>
</div>
