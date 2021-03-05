# Commission task

You can find parameters for commissions fee and limits in `config/services.yml`
```
parameters:
    bcmath.scale: 4
    deposit.business.fee: 0.03
    deposit.private.fee: 0.03
    withdraw.business.fee: 0.5
    withdraw.private.fee: 0.3
    withdraw.private.free.amount.per.week: 1000
    withdraw.private.free.count.per.week: 3
    rate.api.url: https://api.exchangeratesapi.io/latest
```

There are 2 types of strategies. `FixedFeeStrategy` and `RangeStrategy`.

`CommissionCalculator` receive 4 Strategies (for depositPrivate, depositBusiness, withdrawPrivate, withdrawBusiness clients).

It's configured the following way now:
- depositPrivate - `FixedFeeStrategy` with `deposit.private.fee`  
- depositBusiness - `FixedFeeStrategy` with `deposit.business.fee`
- withdrawPrivate - `RangeStrategy` with `withdraw.private.fee`, `withdraw.private.free.amount.per.week`, `withdraw.private.free.count.per.week`   
- withdrawBusiness - `FixedFeeStrategy` with `withdraw.business.fee`

`RangeStrategy` receive `RangeCalculator`. It's `WeeklyRange` (from Monday to Sunday) now.

If you'd like for example Month range or 7 last days. You can implement  `RangeCalculatorInterface` and easily configure.
Also you can create a new different strategy (implement `CommissionInterface`) and pass it to `CommissionCalculator`.  

## Commission printer

Run `php script.php input.csv` to print commissions from csv file. `CommissionPrinter->print(iterable $transactions)` accept any iterable $transactions. 
So if you decide print commission from another source. It's easy configured.

You can configure `CommissionPrinter` to use `FixedRateDataProvider` if you'd like to test something with hardcoded rates. 

## Commands:
- `composer run phpunit` - run phpunit;
- `php script.php input.csv` - run printer script;

## What can be improved?
I missed logic for Base Currency(
