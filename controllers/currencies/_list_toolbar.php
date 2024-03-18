<div data-control="toolbar">
    <?= Ui::popupButton("New Currency", 'onLoadPopupForm')
        ->icon('icon-plus')
        ->primary() ?>

    <?= Ui::popupButton("Enable or Disable", 'onLoadDisableForm')
        ->icon('icon-star')
        ->listCheckedTrigger()
        ->listCheckedRequest()
        ->secondary() ?>

    <?php /*
    <?= Ui::button("Currency Converters", 'shop/currencyconverters')
        ->icon('icon-calculator')
        ->secondary() ?>
    */ ?>
</div>
