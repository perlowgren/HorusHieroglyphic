
# Horus Hierolgyphic

These scripts make it possible to include Egyptian hieroglyphic into webpages.

## Installing:

1. Extract the archive file `NewGardiner.zip` which contains the TTF-font and
   a complete set of PNG-images generated from the font. Make sure the extracted
   directory can be reached by a web browser. The URL to access the folder should
   be used for the option `font-url`.
2. Make sure the files in the `assets` directory can be reached by the browser.

**Example:**

To install the files to a LAMP stack, the files should be copied to
`/var/www/html/public`. Then the directory structure should look like this:

```
|- /var/www/html/public
  |- /var/www/html/public/assets
    |- /var/www/html/public/assets/NewGardiner
    | |- A1.png ... Z9.png
    |- cartouche-left-1.png
    |- cartouche-left-2.png
    |- cartouche-right-1.png
    |- cartouche-right-2.png
    |- hiero.css
```

## Acknowledgements:

* Mark-Jan Nederhof - For creating and kindly sharing the NewGardiner font, which is
  included in this package, and can be found at his web page:
  https://mjn.host.cs.st-andrews.ac.uk/egyptian/fonts/newgardiner.html


## Versions:

2.0 (2022-06-06)
Re-package project to a PHP composer module, and update PHP code to 7.4. 
1.0 (2016-01-07)
Initial release

The scripts are fully free for all private, academic and non-commercial use,
including modification and redistribution. Acknowledgements where appropriate
will of course be appreciated. If you're a company wanting to use the scripts
for commercial purposes, please contact me first.

