# Upgrading from Currency v1 to v2

This guide can be used to help migrate from Responsiv.Pay v1 to v2. Some theme changes are required to since there are new components. Mostly amounts are stored in their base units instead of decimals.

## Key Differences

All amounts are now stored in cents rather than decimals, this means `$1.00` is stored as `100`.

When upgrading, some database columns may need to be adjusted by hand. The following code will convert the `total` column from dollars to cents.

```php
Db::table('responsiv_pay_invoices')->update(['total' => Db::raw('total * 100')]);
```

### Feedback

If there are any changes you would like us to include to make upgrading easier, let us know and we can accommodate them in a new release.

Thanks for reading.
