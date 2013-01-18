Getting Started
===============

The preferred method of downloading and installing EasyRdf, is to use
[Composer], a dependency manager that tracks all dependencies of your project.


First, install composer in your project:

    curl -s https://getcomposer.org/installer | php


Create a composer.json file in your project root:

    {
        "require": {
            "easyrdf/easyrdf": "*"
        }
    }


Install EasyRdf (and any other dependencies) using:

    php composer.phar install


Then to start using EasyRdf in your project, add this to the top of your file:

    <?php
    require 'vendor/autoload.php';

This will load composer's autoloader and make the EasyRdf classes available to your 
programme.


A full basic example looks like this:

    <?php
    require 'vendor/autoload.php';

    $foaf = new EasyRdf_Graph("http://njh.me/foaf.rdf");
    $foaf->load();
    $me = $foaf->primaryTopic();
    echo "My name is: ".$me->get('foaf:name')."\n";


[Composer]:http://getcomposer.org/
