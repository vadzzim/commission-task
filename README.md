# Commission task

You can find parameters for commissions fee and limits in `config/services.yaml`
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

A *commission policy* is a configured strategy instance. Every policy gets its own
instance, configured through the constructor, and is registered under its business
identity (`<operationType>.<userType>`) with the `app.commission_policy` tag:

```
    app.commission.deposit_private:
        class: App\Commission\FixedFeeStrategy
        arguments:
            $fee: '%deposit.private.fee%'
        tags:
            - { name: app.commission_policy, policy: 'deposit.private' }
```

`CommissionCalculator` receives those policies indexed by that tag attribute and
requires one for every combination of operation type and user type:

```
    App\Service\CommissionCalculator:
        class: App\Service\CommissionCalculator
        arguments:
            $policies: !tagged_iterator { tag: app.commission_policy, index_by: policy }
```

It's configured the following way now:
- deposit.private - `FixedFeeStrategy` with `deposit.private.fee`
- deposit.business - `FixedFeeStrategy` with `deposit.business.fee`
- withdraw.private - `RangeStrategy` with `withdraw.private.fee`, `withdraw.private.free.amount.per.week`, `withdraw.private.free.count.per.week`
- withdraw.business - `FixedFeeStrategy` with `withdraw.business.fee`

`RangeStrategy` receive `RangeCalculator`. It's `WeeklyRange` (from Monday to Sunday) now.

If you'd like for example Month range or 7 last days. You can implement  `RangeCalculatorInterface` and easily configure.
Also you can create a new different strategy (implement `CommissionInterface`) and register it as a policy with the `app.commission_policy` tag.

## Commands:
- `docker-compose up -d`
- `docker-compose exec commission-task composer install` - install php dependies
- `docker-compose exec commission-task php app.php app:print-commission assets/input.csv` - print commissions
- `docker-compose exec commission-task php composer run phpunit` - run phpunit;
- `docker-compose down` - stop container
