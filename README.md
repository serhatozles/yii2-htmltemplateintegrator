HTML Template Integrator
========================
[![PayPal Donate](https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=6A78J4T2DYBV4)
[![Latest Stable Version](https://poser.pugx.org/serhatozles/yii2-htmltemplateintegrator/v/stable.svg)](https://packagist.org/packages/serhatozles/yii2-htmltemplateintegrator) [![Total Downloads](https://poser.pugx.org/serhatozles/yii2-htmltemplateintegrator/downloads.svg)](https://packagist.org/packages/serhatozles/yii2-htmltemplateintegrator) [![Latest Unstable Version](https://poser.pugx.org/serhatozles/yii2-htmltemplateintegrator/v/unstable.svg)](https://packagist.org/packages/serhatozles/yii2-htmltemplateintegrator) [![License](https://poser.pugx.org/serhatozles/yii2-htmltemplateintegrator/license.svg)](https://packagist.org/packages/serhatozles/yii2-htmltemplateintegrator)
------------
Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist serhatozles/yii2-htmltemplateintegrator "*"
```

or add

```
"serhatozles/yii2-htmltemplateintegrator": "*"
```

to the require section of your `composer.json` file.

What is This Extension doing?
--------------
This extension can help you for your html template integration to Yii2.

After one click, Your asset and layout files will be ready.

Properties
--------------
1. Assets
2. Layouts 
3. Controller
4. Actions

It's making all of them.

Usage
-----
Firstly, you have to put your template folder into @app/template.

And you add into @app/config/main.php
```
'controllerMap' => [
    'integrator' => [
        'class' => 'serhatozles\themeintegrator\Controller',
    ]
],
```

Example:

```
/frontend
/frontend/template
/frontend/template/yourtemplate
/frontend/template/yourtemplate/css
/frontend/template/yourtemplate/image
/frontend/template/yourtemplate/...
/frontend/template/yourtemplate/index.html
/frontend/template/yourtemplate/...
```

After, Open to web "integrator/define"

That's it.

Screenshot:
-----

![Screenshot](https://lh3.googleusercontent.com/BcxsybZL5VPpdgHOXNBasmB0H49VEPAM2APwY2EdSTQ=w1044-h474-no)
![Screenshot](https://lh3.googleusercontent.com/-3JYhRkEYZ-Y/VKQLeVpUz_I/AAAAAAAAAbs/5-9YyUCpfgM/w646-h530-no/HTML%2BTemplate%2BIntegrator%2B2.png)
![Screenshot](https://lh3.googleusercontent.com/-SQYM4nyDzeg/VKQLeMus-5I/AAAAAAAAAbw/joCW8xPK6Po/w751-h530-no/HTML%2BTemplate%2BIntegrator%2B3.png)
![Screenshot](https://lh4.googleusercontent.com/hh_GXCvhZVo64fqWgL5dbeffhF3Hy2Alj7T4WQjN-e0=w762-h530-no)
![Screenshot](https://lh4.googleusercontent.com/-cokBAxxAgQY/VIgJg4BYXVI/AAAAAAAAAZU/4yQyN1ARDY4/w1042-h217-no/HTML%2BTemplate%2BIntegrator%2B3.png)

[See](https://plus.google.com/u/0/photos/109846768885330232680/albums/6091128953088590609)
