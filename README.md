## A Composer installer for ZendFramework(1) modules

[![Build Status](https://travis-ci.org/easybiblabs/zf1module-installer.png?branch=master)](https://travis-ci.org/easybiblabs/zf1module-installer)

[![Latest Stable Version](https://poser.pugx.org/easybib/zf1module-installer/v/stable.png)](https://packagist.org/packages/easybib/zf1module-installer)

[![Total Downloads](https://poser.pugx.org/easybib/zf1module-installer/downloads.png)](https://packagist.org/packages/easybib/zf1module-installer)

[![Montly Downloads](https://poser.pugx.org/easybib/zf1module-installer/d/monthly.png)](https://packagist.org/packages/easybib/zf1module-installer)

[![Daily Downloads](https://poser.pugx.org/easybib/zf1module-installer/d/daily.png)](https://packagist.org/packages/easybib/zf1module-installer)

[![Latest Unstable Version](https://poser.pugx.org/easybib/zf1module-installer/v/unstable.png)](https://packagist.org/packages/easybib/zf1module-installer)


This is a WIP – so handle with care!

### How does it work?

Say, you have a module called **admin**:

You put the following `composer.json` file into this module:

    {
        "name":"example/admin",
        "description": "Admin module!",
        "type":"zf1-module",
        "authors":[
            {
                "name":"John Doe",
                "email":"john@example.org"
            }
        ],
        "require": {
            "php": ">=5.3.0",
            "easybib/zf1module-installer": "*"
        }
    }

The other important bit is [`easybib/zf1module-installer`](packagist.org/packages/easybib/zf1module-installer).

In your application, add your own module as a dependency:

    {
        "name":"example/main",
        "description": "My application",
        "authors":[
            {
                "name":"John Doe",
                "email":"john@example.org"
            }
        ],
        "require": {
            "php": ">=5.3.0",
            "example/admin": "*"
        }
    }

When you run `php composer.phar install`, the `admin` module should end up in your modules folder.
