# laravel-tk-kit

## Установка

```
composer requere 
```
```php
$kit = New Kit(); 
```

##isCity()
Проверяет осуществляется ли доставка в переданный город.
Если доставка в переданный город не осуществляется то возвращает false

return array

```
$kit->isCity('Калининград');
```

##getCityList()
Возвращает список населённых пунктов с которыми работает ТК КИТ
return array

```
$kit->getCityList();
```

##priceOrder
Возвращает стоимость и срок перевозки по указанному маршруту
массив дата состоит из
````
[
  I_DELIVER=>0
  I_PICK_UP=>1
  WEIGHT=>30
  VOLUME=>0.6
  SLAND=>RU
  SZONE=>0000008610
  SCODE=>860001000000
  SREGIO=>86
  RLAND=>RU
  RZONE=>0000008910
  RCODE=>890000700000
  RREGIO=>89
  KWMENG=>1
  LENGTH>=84.34
  WIDTH=>84.34
  HEIGHT=>84.34
  GR_TYPE=>
  LIFNR=>
  PRICE=>
  WAERS=>RUB
];
````
Продробнее https://ekaterinburg.tk-kit.ru/about/developers/api?ver=1.1#p3.8
```
$kit->priceOrder($data, 'Калиниград', 'Москва');
```

##priceOrderSlim()
Возвращает стоимость и срок перевозки по указанному маршруту

Slim - без проверки городов, на вход подаются данные пришедшие из isCity

Продробнее https://ekaterinburg.tk-kit.ru/about/developers/api?ver=1.1#p3.8

```
$kit->priceOrderSlim($data, 'Калиниград', 'Москва');
```

