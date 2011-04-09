<?php
class Config {
    //----------------------------------------------------------
    // Configuration for wp2docbook 
    //----------------------------------------------------------

    // Full path to your WordPress install
    public static $WP_PATH = '/var/www/sample-blog';

    // Title of your book
    public static $BOOK_TITLE = "Shakespeare's Sonnets";

    // Optionally designate a page/post to be the preface of the book
    public static $PREFACE_PAGE = 'About';

    // Author First Name
    public static $AUTHOR_FIRST = 'William';

    // Author Last Name
    public static $AUTHOR_LAST = 'Shakespeare';

    // Include copyright in title page? 
    public static $INCLUDE_COPYRIGHT = true;

    // Copyright year
    public static $COPY_YEAR = 2011;

    // Copyright holder
    public static $COPY_HOLDER = 'William Shakespeare';
}
?>
