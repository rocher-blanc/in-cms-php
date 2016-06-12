Pour installer le CMS, créer un fichier *composer.json* comme ceci :

	{
		"name": "jweb/site",
		"authors": [
			{
				"name": "Guillaume DEVELTER",
				"email": "guillaume@jweb-creation.fr"
			}
		],
		"autoload": {
			"psr-4": {
				"Project\\" : "Project"
			}
		},
		"minimum-stability": "dev",
		"require": {
			"jweb/cms": "dev-master"
		},
		"repositories": [
			{
				"type": "vcs",
				"url": "https://github.com/JWebCreation/cms.git",
				"name": "jweb/cms"
			}
		],
		"scripts": {
			"post-update-cmd": "App\\Kernel\\Install::postUpdate",
			"post-install-cmd": "App\\Kernel\\Install::postInstall"
		},
		"extra": {
			"asset-installer-paths": {
				"npm-asset-library": "web/assets/vendor",
				"bower-asset-library": "web/assets/vendor"
			},
			"asset-repositories": [
			  {
				"type": "bower-vcs",
				"url": "https://github.com/JWebCreation/cmsmedias.git",
				"name": "bower-asset/cmsmedias"
			  }
			]
		}
	}

Pour installer, ensuite, lancer un :
	composer update
	

