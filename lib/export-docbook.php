<?php
/*
 * Copyright (c) 2011 Andrew E. Bruno <aeb@qnot.org> 
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once('config.php');

/**
 * This class provides methods for exporting WordPress blog posts and converting
 * them into a quasi-DocBook format.
 */
class ExportDocbook {

    /**
     * This method encapsulates the logic for organizing blog posts into
     * chapters and sections of a book. This is a very basic example which 
     * fetches all posts from the database and organizes them based on their
     * tags (only uses the first one). 
     * @return hash - keys are the chapters and values is array of post IDs 
     * which will be the sections of the book
     */
    public static function organizeBook($wpdb) {
        $book = array();

        $result = self::fetchPosts($wpdb);
        foreach($result as $post) {
            $tags = get_the_category($post->ID);
            $title = get_the_title($post->ID);

            $key = 'Untagged';
            if(count($tags) > 0) {
                $key = $tags[0]->name;
            }

            // Optionally can configure a post to be the preface
            if($title === Config::$PREFACE_PAGE || $post->ID === Config::$PREFACE_PAGE) {
                $key = 'preface';
            }

            $posts[$key][] = $post->ID;
        }

        return $posts;
    }

    /**
     * Output all posts as a DocBook book. 
     * @param wpdb wordpress database handle
     * @param uri output file uri
     * @param book organized book data structure (hash)
     */
    public static function outputBook($wpdb, $uri) {
        $book = self::organizeBook($wpdb);

        $xml = new XMLWriter();
        $xml->openURI($uri);

        self::startDocbook(&$xml);

        if(array_key_exists('preface', $book)) {
            $pid = $book['preface'][0];
            $content = self::getPostContent($wpdb, $pid);

            $xml->startElement('preface'); 
            $xml->writeAttributeNS('xml', 'id', null, 'preface-1');
                $xml->writeElement('title', get_the_title($pid)); 
                $xml->writeElement('subtitle', get_the_time('F jS, Y', $pid)); 
                $xml->writeRaw($content);
            $xml->endElement();
            unset($book['preface']);
        }

        $count = 1;
        foreach(array_keys($book) as $key) {
            $xml->startElement('chapter'); 
            $xml->writeAttributeNS('xml', 'id', null, "chapter-$count");
            $xml->writeElement('title', $key); 
            foreach($book[$key] as $id) {
                $content = self::getPostContent($wpdb, $id);
                $xml->startElement('section'); 
                $xml->writeAttributeNS('xml', 'id', null, "sect-$id");
                    $xml->writeElement('title', get_the_title($id)); 
                    $xml->writeElement('subtitle', get_the_time('F jS, Y', $id)); 
                    $xml->writeRaw($content);
                $xml->endElement();
            }
            $xml->endElement();
            $count++;
        }

        self::endDocbook(&$xml);
    }

    /**
     * Output all posts as DocBook articles. 
     * @param wpdb wordpress database handle
     * @param uri output file uri
     */
    public static function outputArticles($wpdb, $uri) {
        $xml = new XMLWriter();
        $xml->openURI($uri);

        self::startDocbook(&$xml);

        $result = self::fetchPosts($wpdb);
        foreach($result as $post) {
            $id = $post->ID;
            $xml->startElement('article'); 
            $xml->writeAttributeNS('xml', 'id', null, "article-$id");
                $xml->startElement('articleinfo'); 
                    $xml->writeElement('title', get_the_title($id));
                    $xml->writeElement('pubdate', get_the_time('F jS, Y', $id));

                    $xml->startElement('author'); 
                        $xml->startElement('othername'); 
                            $xml->writeAttribute('role', 'display_name');
                            $xml->text(get_the_author_meta('display_name', $post->post_author)); 
                        $xml->endElement();
                    $xml->endElement();

                    $tags = get_the_category($id);

                    if(count($tags) > 0) {
                        $tarray = array();
                        $xml->startElement('keywordset'); 
                            foreach($tags as $t) {
                                $xml->writeElement('keyword', $t->name);
                                $tarray[] = $t->name;
                            }
                        $xml->endElement();
                        $xml->writeElement('subtitle', implode(', ', $tarray));
                    }

                $xml->endElement();

                $content = self::getPostContent($wpdb, $id);
                $xml->writeRaw($content);
            $xml->endElement();
        }

        self::endDocbook(&$xml);
    }

    /**
     * Fetch the content of the post and apply WordPress filters 
     * @param wpdb wordpress database handle
     * @param id of post to fetch
     * @return the HTML content of the post
     */
    private static function getPostContent($wpdb, $id) {
        $result = self::fetchPosts($wpdb, $id);

        if(count($result) !== 1) {
            error_log("Post not found with id: $id");
            return "";
        }

        $post = $result[0];

        $content = apply_filters('the_content', $post->post_content);
        $content = force_balance_tags($content);
        $content = str_replace(']]>', ']]&gt;', $content);

        return $content;
    }

    /**
     * Start the DocBook XML
     */
    private static function startDocbook($xml) {
        $xml->startDocument("1.0");

        // TODO add in proper namspace (need to modify XSLT). Namespaces are PITA
        // $xml->startElementNS(null, 'book', 'http://docbook.org/ns/docbook'); 

        $xml->startElement('book'); 
        $xml->writeAttributeNS('xml', 'id', null, uniqid('book-'));
        $xml->startElement('info');
            $xml->writeElement('title', Config::$BOOK_TITLE);
            $xml->startElement('author');
                $xml->writeElement('firstname', Config::$AUTHOR_FIRST);
                $xml->writeElement('surname', Config::$AUTHOR_LAST);
            $xml->endElement();
            if(Config::$INCLUDE_COPYRIGHT) {
                $xml->startElement('copyright');
                    $xml->writeElement('year', Config::$COPY_YEAR);
                    $xml->writeElement('holder', Config::$COPY_HOLDER);
                $xml->endElement();
            }
        $xml->endElement();
    }

    /**
     * End the DocBook XML
     */
    private static function endDocbook($xml) {
        $xml->endElement();
        $xml->endDocument();
        $xml->flush();
    }

    /**
     * Private method to fetch posts from the WordPress database. 
     * @param wpdb wordpress database handle
     * @param id optionally pass in ID of post to fetch
     */
    private function fetchPosts($wpdb, $id=null) {
        $query = "
        select 
            ID,
            post_content,
            post_author
        from 
            $wpdb->posts 
        where 
            post_status='publish' and 
            (post_type='post' or post_type='page') 
        ";

        if($id !== null) $query .= " and ID = $id ";
        $query .= " order by post_date asc";

        return $wpdb->get_results($query);
    }
}
?>
