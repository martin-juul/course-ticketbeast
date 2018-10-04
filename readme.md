# TicketBeast

## Getting Started

It is important you configure your environment to use PHP 7.1 or older. There's some bugs with newer version, when starting the course on Laravel 5.3

In homestead this is quite easy. Open the file Homestead.yaml, and edit your site mapping like this:

```~yaml
sites:
  - map: ticketbeast.test
    to: /home/vagrant/code/public
    php: "7.1"
```