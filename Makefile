#-------------------------------------------------------------------------------
# Makefile for converting WordPress blog into PDF
#-------------------------------------------------------------------------------

#
# Required software
#
XSLTPROC=/usr/bin/xsltproc
FOP=/usr/bin/fop
PHP=/usr/bin/php
DB_STYLE_SHEET=/usr/share/xml/docbook/stylesheet/docbook-xsl/fo/docbook.xsl


all: prepare export-docbook docbook-final fo pdf

prepare:
	mkdir -p scratch

export-docbook: prepare
	${PHP} wp2docbook.php -b -o scratch/quasi-docbook.xml

#------------------------------------------------------------------------------
# XXX Need to configure the XSLT parameters:
#
#    media.file.path - Full path to WordPress install (/var/www/blog)
#    blog.url        - URL of your blog (http://localhost/blog)
#------------------------------------------------------------------------------
docbook-final: export-docbook
	${XSLTPROC} \
	--stringparam media.file.path '/var/www/sample-blog' \
	--stringparam blog.url 'http://localhost/sample-blog/' \
	wp-html2docbook.xsl scratch/quasi-docbook.xml > scratch/docbook-final.xml

#------------------------------------------------------------------------------
# Edit the XSLT parameters to adjust resulting PDF file
#------------------------------------------------------------------------------
fo: docbook-final
	${XSLTPROC} \
    --stringparam page.width 6in \
    --stringparam page.height 9in \
    --stringparam page.margin.inner 1.0in \
    --stringparam page.margin.outer 0.8in \
    --stringparam body.start.indent 0pt \
    --stringparam body.font.family  Times \
    --stringparam title.font.family Times \
    --stringparam dingbat.font.family Times \
    --stringparam generate.toc 'book toc title' \
    --stringparam hyphenate false \
    ${DB_STYLE_SHEET} \
    scratch/docbook-final.xml > scratch/book.fo

pdf: fo
	${FOP} -c conf/userconf.xconf scratch/book.fo book.pdf

clean:
	rm -Rf scratch/ book.pdf
