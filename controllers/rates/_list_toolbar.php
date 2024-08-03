<div data-control="toolbar loader-container">
    <?= Ui::button("New Rate", 'responsiv/currency/rates/create')
        ->icon('icon-plus')
        ->primary() ?>

    <?= Ui::ajaxButton("Generate Pairs", 'onGeneratePairs')
        ->loadingMessage("Loading...")
        ->confirmMessage("This will create missing currency pairs for the primary currency to all other currencies. Continue?")
        ->icon('icon-refresh')
        ->secondary() ?>

    <?php /*
    <div class="toolbar-divider"></div>

    <?= Ui::button("Manage Converters", 'responsiv/currency/converters')
        ->icon('icon-line-chart')
        ->secondary() ?>

    <?= Ui::ajaxButton("Request Rates", 'onRequestRates')
        ->loadingMessage("Loading...")
        ->confirmMessage("This request the latest exchange rates from the currency converters. Continue?")
        ->icon('icon-download')
        ->secondary() ?>
    */ ?>

    <div class="toolbar-divider"></div>

    <?= Ui::ajaxButton("Delete", 'onDelete')
        ->listCheckedTrigger()
        ->listCheckedRequest()
        ->icon('icon-delete')
        ->secondary()
        ->confirmMessage("Are you sure?") ?>
</div>
