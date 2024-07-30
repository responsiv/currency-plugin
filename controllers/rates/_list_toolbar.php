<div data-control="toolbar loader-container">
    <?= Ui::button("New Rate", 'responsiv/currency/rates/create')
        ->icon('icon-plus')
        ->primary() ?>

    <?= Ui::button("Generate Pairs", 'responsiv/currency/rates/create')
        ->icon('icon-refresh')
        ->secondary() ?>

    <div class="toolbar-divider"></div>

    <?= Ui::button("Request Rates", 'responsiv/currency/rates/create')
        ->icon('icon-download')
        ->secondary() ?>

    <?= Ui::button("Manage Converters", 'responsiv/currency/converters')
        ->icon('icon-line-chart')
        ->secondary() ?>

    <div class="toolbar-divider"></div>

    <?= Ui::ajaxButton("Delete", 'onDeleteSelected')
        ->listCheckedTrigger()
        ->listCheckedRequest()
        ->icon('icon-delete')
        ->secondary()
        ->confirmMessage("Are you sure?") ?>
</div>
