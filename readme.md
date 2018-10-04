# TicketBeast

## Getting Started

It is important you configure your environment to use PHP 7.1 or older. There's some bugs with newer versions, when starting the course on Laravel 5.3

In homestead this is quite easy. Open the file Homestead.yaml, and edit your site mapping like this:

```~yaml
sites:
  - map: ticketbeast.test
    to: /home/vagrant/code/public
    php: "7.1"
```

## Stripe

Create an account here: [https://dashboard.stripe.com/register](https://dashboard.stripe.com/register)

You need to verify it with a phone number.

### API
- [Api Docs](https://stripe.com/docs)
- [Create a card token](https://stripe.com/docs/api?lang=php#create_card_token)

### Wiping test data

Go to [https://dashboard.stripe.com/account/data](https://dashboard.stripe.com/account/data)
\- in the bottom section, click _Delete all test data..._
