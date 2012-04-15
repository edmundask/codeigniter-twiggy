# Twiggy - Twig template engine implementation for CodeIgniter

Twiggy is not just a simple implementation of Twig template engine for CodeIgniter. It supports themes, layouts, templates for regular apps and also for apps that use HMVC (module support). 
It is supposed to make life easier for developing and maitaining CodeIgniter applications where themes and nicely structured templates are necessary.

## Why Should I Care?

Twig by itself is a very powerful and flexible templating system but with CodeIgniter it is even cooler! With Twiggy you can separately set the theme, layout and template for each page. 
What is even more interesting, this does not replace CodeIgniter's default Views, so you can still load views as such: `$this->load->view()`.

# Requirements

* PHP 5.2.4+
* [CodeIgniter](http://codeigniter.com/) 2.x

# How To Use It

## 1. Load library (as a spark)

`$this->load->spark('twiggy/x.x.x');` where `x.x.x` is the version you want to load (assuming you have it installed).

## 2. Set up dir structure

1. Create a directory structure:

	```
    +-{APPPATH}/
    | +-themes/
    | | +-default/
    | | | +-_layouts/
	```

	NOTE: `{APPPATH}` is the folder where all your controllers, models and other neat stuff is placed.
	By default that folder is called `application`.

2. Create a default layout `index.html.twig` and place it in _layouts  folder:

	```
	<!DOCTYPE html>
	<html lang="en">
		<head>
			<meta charset="utf-8">
			<!--[if lt IE 9]>
			<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
			<![endif]-->
			<title>Default layout</title>
		</head>
		<body>

			{% block content %}



			{% endblock %}
			
		</body>
	</html>
	```

3. Create a default template file `index.html.twig` at the root of `default` theme folder:

	```
	{% extends _layout %}

	{% block content %}

		Default template file.

	{% endblock %}
	```

4. You should end up with a structure like this:

	```
    +-{APPPATH}/
    | +-themes/
    | | +-default/
    | | | +-_layouts/
    | | | | +-index.hml.twig
    | | | +-index.html.twig
	```

## 3. Display the template

`$this->twiggy->display();`

## 4. What's next?

In the example above we only displayed the default template and layout. Obviously, you can create as many layouts and templates as you want.
For example, create a new template file `welcome.html.twig` and load it before sending the output to the browser.

```
// Whoah, methoding chaining FTW!
$this->twiggy->template('welcome')->display();
```

Notice that you only need to specify the name of the template (without the extension `*.html.twig`).

There is much more cool stuff that you should check out by visiting the [wiki](https://github.com/edmundask/codeigniter-twiggy/wiki).

# CHANGELOG

### 0.8.5

* Changed `display()` and `render()` methods a little bit to accept a parameter. From now on you can set the template file without the `template()` method. For example: `$this->twiggy->display('admin/dashboard');` instead of `$this->twiggy->template('admin/dashboard')->display()`.
* Added `rendered()` method to check whether a template has already been rendered/displayed using `display()` or `render()`.
* Fixed a bug where calling `func_get_args()` function as a parameter in another function would cause a fatal error: `Fatal error: func_get_args(): Can’t be used as a function parameter in <...>`.

### 0.8.4

* Fixed a bug where template locations would not be updated correctly after loading a different theme.
* Changed `autoescape` Twig environment option to `FALSE` in the config file as the default value.

### 0.8.3

* Fixed a bug where global variables would not be available (accessible)
* Added helper methods for dealing with the title tag.
* Added helper methods for setting meta data (meta tags).

### 0.8.2

* Fixed a problem with Twig cache. Caching should now work as expected.

### 0.8.1

* Added `unset_data()` method to unset a particular variable, given a key.
* Fixed a bug where calling render() would throw `Twig_Error_Loader` exception due to missing file extention.
* Added a private method _load() to load the template and return output object where previously this was done both in render() and display() methods separately.
* Added `Twig_Error_Loader` exception handling in render() method.

# DONATE

[![test](http://www.pledgie.com/campaigns/16940.png?skin_name=chrome)](http://www.pledgie.com/campaigns/16940)

# COPYRIGHT

Copyright (c) 2012 Edmundas Kondrašovas

Permission is hereby granted, free of charge, to any person obtaining a copy 
of this software and associated documentation files (the "Software"), to deal 
in the Software without restriction, including without limitation the rights 
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell 
copies of the Software, and to permit persons to whom the Software is 
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in 
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN 
THE SOFTWARE.