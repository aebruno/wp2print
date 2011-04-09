<?xml version="1.0"?>
<!--
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
-->
<xsl:stylesheet version="1.0" 
      xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
      xmlns:str="http://exslt.org/strings" 
      extension-element-prefixes="str">

<!-- Custom Parameters -->
<xsl:param name="media.file.path"></xsl:param>
<xsl:param name="blog.url"></xsl:param>

<!-- div can sometimes be containers for images -->
<xsl:template match="div">
    <xsl:choose>
    <xsl:when test="a">
      <xsl:apply-templates select="a">
        <xsl:with-param name="caption" select="p/text()"/>
      </xsl:apply-templates>
    </xsl:when>
    <xsl:when test="img">
      <xsl:apply-templates select="img">
        <xsl:with-param name="caption" select="p/text()"/>
      </xsl:apply-templates>
    </xsl:when>
    <xsl:otherwise>
      <xsl:apply-templates />
    </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<xsl:template match="a">
    <xsl:param name="caption"/>
    <xsl:choose>
    <xsl:when test="img">
      <xsl:apply-templates select="img">
        <xsl:with-param name="caption" select="$caption"/>
        <xsl:with-param name="url" select="@href"/>
      </xsl:apply-templates>
    </xsl:when>
    <xsl:otherwise>
        <ulink url="{@href}">
            <xsl:apply-templates/>
        </ulink>
    </xsl:otherwise>
    </xsl:choose>
</xsl:template>

<!-- convert images to mediaobjects. Hard coded size to 4x3in. -->
<xsl:template match="img">
    <xsl:param name="caption"/>
    <xsl:param name="url"/>
    <para>
    <mediaobject>
        <imageobject>
        <xsl:choose>
        <xsl:when test="$url">
            <imagedata align="center" 
                       fileref="{$media.file.path}/{substring-after($url, $blog.url)}" 
                       width="4.0in" 
                       depth="3.0in" 
                       scalefit="1" 
                       format="JPG" />
        </xsl:when>
        <xsl:otherwise>
            <imagedata align="center" 
                       fileref="{$media.file.path}/{substring-after(@src, $blog.url)}" 
                       width="4.0in" 
                       depth="3.0in" 
                       scalefit="1" 
                       format="JPG" />
        </xsl:otherwise>
        </xsl:choose>
        </imageobject>
    <xsl:if test="$caption">
        <caption>
            <para><xsl:value-of select="$caption" /></para>
        </caption>
    </xsl:if>
    </mediaobject>
    </para>
</xsl:template>

<xsl:template match="p">
  <para><xsl:apply-templates/></para>
</xsl:template>

<xsl:template match="em">
  <emphasis><xsl:apply-templates/></emphasis>
</xsl:template>

<xsl:template match="br">
  <xsl:apply-templates/>
</xsl:template>

<xsl:template match="ul">
 <itemizedlist mark="opencircle">
  <xsl:apply-templates/>
 </itemizedlist>
</xsl:template>

<xsl:template match="ol">
 <orderedlist numeration="arabic">
  <xsl:apply-templates/>
 </orderedlist>
</xsl:template>

<xsl:template match="li">
 <listitem><para>
  <xsl:apply-templates/>
 </para></listitem>
</xsl:template>

<!-- copy all other elements, attributes, etc. as is -->
<xsl:template match="@*|node()">
    <xsl:copy>
        <xsl:apply-templates select="@*|node()"/>
    </xsl:copy>
</xsl:template>

</xsl:stylesheet>
