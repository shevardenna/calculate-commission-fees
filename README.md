## How to run project

````
git clone https://github.com/shevardenna/calculate-commission-fees.git
cd calculate-commission-fees
cp .env.example .env
````
Set CUREENCY_RATES_API value in .env file
````
composer install
````
After this, you can calculate commission fees by running this command:
````
 php artisan calculate:commissions {url}
````
For example, you can use this CSV URL: https://gist.githubusercontent.com/shevardenna/fc5e974bfce07fc670ccf891ef007ed2/raw/6297c75ea3d75fafedd36eb9e7dacd4df9554a56/clients.csv

## How to run test
````
 php artisan test
````
Test takes input:
````
2014-12-31,4,private,withdraw,1200.00,EUR
2015-01-01,4,private,withdraw,1000.00,EUR
2016-01-05,4,private,withdraw,1000.00,EUR
2016-01-05,1,private,deposit,200.00,EUR
2016-01-06,2,business,withdraw,300.00,EUR
2016-01-06,1,private,withdraw,30000,JPY
2016-01-07,1,private,withdraw,1000.00,EUR
2016-01-07,1,private,withdraw,100.00,USD
2016-01-10,1,private,withdraw,100.00,EUR
2016-01-10,2,business,deposit,10000.00,EUR
2016-01-10,3,private,withdraw,1000.00,EUR
2016-02-15,1,private,withdraw,300.00,EUR
2016-02-19,5,private,withdraw,3000000,JPY
````
It calculates the commission fees based on the given exchange rates:
````
EUR:USD - 1:1.1497
EUR:JPY - 1:129.53
````
and result matches to this output:
````
0.60
3.00
0.00
0.06
1.50
0
0.70
0.30
0.30
3.00
0.00
0.00
8612
````
