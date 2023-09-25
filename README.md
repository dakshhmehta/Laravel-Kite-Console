# Installation
1. Change .env with database details
2. Run `php artisan migrate`
3. Setup Zerodha Application and retrive the key and secret key and put it in .env file as `ZERODHA_API_KEY` and `ZERODHA_API_SECRET`
4. Run `php artisan seed:instruments NSE`
4. Visit http://localhost:8000/auth/zerodha and proceed with login

Step #4 is required everyday before executing commands.

5. To save data, run `php artisan monitor:save --symbols=ITC` which will fetch data from the beginning and save it to database.