<div data-control="toolbar">
    <?= Ui::popupButton("New Currency", 'onLoadPopupForm')
        ->icon('icon-plus')
        ->primary() ?>

    <?= Ui::popupButton("Enable or Disable", 'onLoadDisableForm')
        ->icon('icon-star')
        ->listCheckedTrigger()
        ->listCheckedRequest()
        ->secondary() ?>
</div>
