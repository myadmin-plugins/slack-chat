# MyAdmin Slack Chat Plugin

[![Build Status](https://github.com/detain/myadmin-slack-chat/actions/workflows/tests.yml/badge.svg)](https://github.com/detain/myadmin-slack-chat/actions/workflows/tests.yml)
[![Latest Stable Version](https://poser.pugx.org/detain/myadmin-slack-chat/version)](https://packagist.org/packages/detain/myadmin-slack-chat)
[![Total Downloads](https://poser.pugx.org/detain/myadmin-slack-chat/downloads)](https://packagist.org/packages/detain/myadmin-slack-chat)
[![License](https://poser.pugx.org/detain/myadmin-slack-chat/license)](https://packagist.org/packages/detain/myadmin-slack-chat)

Slack Chat Bot integration plugin for the [MyAdmin](https://github.com/detain/myadmin) control-panel framework. This plugin provides event-driven hooks for Slack messaging, admin menu integration, and dynamic requirement loading through the Symfony EventDispatcher component.

## Features

- Event-driven architecture using Symfony EventDispatcher
- Admin menu integration with ACL support
- Dynamic requirement loading for Slack API classes
- Lightweight plugin with zero runtime overhead when inactive

## Requirements

- PHP 8.2 or higher
- ext-soap
- Symfony EventDispatcher 5.x, 6.x, or 7.x

## Installation

```sh
composer require detain/myadmin-slack-chat
```

## Usage

The plugin registers itself through the MyAdmin plugin system. Hook registration is handled automatically via the `getHooks()` method, which returns an array of event names mapped to static handler callables.

## Running Tests

```sh
composer install
vendor/bin/phpunit
```

## License

This package is licensed under the [LGPL-2.1](LICENSE) license.
