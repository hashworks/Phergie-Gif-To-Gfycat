# PhergieGifToGfycat

[Phergie](http://github.com/phergie/phergie-irc-bot-react/) plugin to convert all posted GIF links into WEBM using [Gfycat](https://gfycat.com/).

## Install

To install via [Composer](http://getcomposer.org/), use the command below, it will automatically detect the latest version and bind it with `~`.

```
composer require hashworks/PhergieGifToGfycat
```

See Phergie documentation for more information on
[installing and enabling plugins](https://github.com/phergie/phergie-irc-bot-react/wiki/Usage#plugins).

## Configuration

```php
// dependency
new \WyriHaximus\Phergie\Plugin\Dns\Plugin,
new \WyriHaximus\Phergie\Plugin\Http\Plugin(array('dnsResolverEvent' => 'dns.resolver')),
new \hashworks\Phergie\Plugin\GifToGfycat\Plugin
```
