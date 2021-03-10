# Commission task

You can find parameters for commissions fee and limits in `config/services.yml`
```
parameters:
    bcmath.scale: 4
    base.currency: EUR
    deposit.business.fee: 0.03
    deposit.private.fee: 0.03
    withdraw.business.fee: 0.5
    withdraw.private.fee: 0.3
    withdraw.private.free.amount.per.week: 1000
    withdraw.private.free.count.per.week: 3
    rate.api.url: https://api.exchangeratesapi.io/latest
```

There are 2 types of strategies. `FixedFeeStrategy` and `RangeStrategy`.

`CommissionCalculator` receive 4 parameters with options (for depositPrivate, depositBusiness, withdrawPrivate, withdrawBusiness clients).

```
    App\Service\CommissionCalculator:
        class: App\Service\CommissionCalculator
        arguments:
            $data:
                depositPrivate:
                    strategy: App\Commission\FixedFeeStrategy
                    fee: '%deposit.private.fee%'
                depositBusiness:
                    strategy: App\Commission\FixedFeeStrategy
                    fee: '%deposit.business.fee%'
                withdrawPrivate:
                    strategy: App\Commission\RangeStrategy
                    fee: '%withdraw.private.fee%'
                    freeAmountPerWeek: '%withdraw.private.free.amount.per.week%'
                    freeWithdrawCountPerWeek: '%withdraw.private.free.count.per.week%'
                withdrawBusiness:
                    strategy: App\Commission\FixedFeeStrategy
                    fee: '%withdraw.business.fee%'
```

It's configured the following way now:
- depositPrivate - `FixedFeeStrategy` with `deposit.private.fee`  
- depositBusiness - `FixedFeeStrategy` with `deposit.business.fee`
- withdrawPrivate - `RangeStrategy` with `withdraw.private.fee`, `withdraw.private.free.amount.per.week`, `withdraw.private.free.count.per.week`   
- withdrawBusiness - `FixedFeeStrategy` with `withdraw.business.fee`

`RangeStrategy` receive `RangeCalculator`. It's `WeeklyRange` (from Monday to Sunday) now.

If you'd like for example Month range or 7 last days. You can implement  `RangeCalculatorInterface` and easily configure.
Also you can create a new different strategy (implement `CommissionInterface`) and pass it to `CommissionCalculator`.   

## Commands:
- `docker build -t commission-task .`
- `docker run -it --rm --name my-running-script -v "$PWD":/usr/src/myapp -w /usr/src/myapp commission-task php application.php app:print-commission assets/input.csv` - print commissions
- `docker run -it --rm --name my-running-script -v "$PWD":/usr/src/myapp -w /usr/src/myapp commission-task php composer run phpunit` - run phpunit;
