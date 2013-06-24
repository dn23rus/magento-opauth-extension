# Oggetto Opauth extension for Magento

To install: set up your root composer.json in your project like this

```json
{
    "name": "your-vendor-name/module-or-project-name",
    "description": "A short one line description of your module or project",
    "require": {
        "oggetto/magento-opauth": "*"
    },
    "repositories": [
        {
            "type": "composer",
            "url": "http://packages.firegento.com"
        }
    ],
    "extra":{
        "magento-root-dir": "./"
    }
}
```

Other information about installation you can find at [Magento Composer Installer](https://github.com/magento-hackathon/magento-composer-installer)
