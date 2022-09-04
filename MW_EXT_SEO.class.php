<?php

namespace MediaWiki\Extension\CMFStore;

use ContentHandler, DateTime, Html, OutputPage, ParserOutput, Revision, User;

/**
 * Class MW_EXT_SEO
 */
class MW_EXT_SEO
{
  /**
   * Render function.
   *
   * @param OutputPage $out
   * @param ParserOutput $parserOutput
   *
   * @return bool
   * @throws \ConfigException
   * @throws \MWException
   */
  public static function onRenderSEO(OutputPage $out, ParserOutput $parserOutput)
  {
    // Get custom info.
    $getSiteURL = MW_EXT_Kernel::getConfig('Server');
    $getSiteName = MW_EXT_Kernel::outClear(MW_EXT_Kernel::getConfig('Sitename'));
    $getSiteEmail = MW_EXT_Kernel::getConfig('EmergencyContact');
    $getSiteLogo = MW_EXT_Kernel::getConfig('Logo');
    $getFavicon = MW_EXT_Kernel::getConfig('Favicon');

    // Get extension info.
    $getSitePhone = MW_EXT_Kernel::getConfig('EXT_SEO_Phone');
    $getPublisher = MW_EXT_Kernel::getConfig('EXT_SEO_Publisher');
    $getPublisherLogo = MW_EXT_Kernel::getConfig('EXT_SEO_PublisherLogo');
    $getManifest = MW_EXT_Kernel::getConfig('EXT_SEO_Manifest');
    $getURL_Vk = MW_EXT_Kernel::getConfig('EXT_SEO_URL_Vk');
    $getURL_Facebook = MW_EXT_Kernel::getConfig('EXT_SEO_URL_Facebook');
    $getURL_Twitter = MW_EXT_Kernel::getConfig('EXT_SEO_URL_Twitter');
    $getURL_Discord = MW_EXT_Kernel::getConfig('EXT_SEO_URL_Discord');
    $getThemeColor = MW_EXT_Kernel::getConfig('EXT_SEO_ThemeColor');
    $getMSTileColor = MW_EXT_Kernel::getConfig('EXT_SEO_MSTileColor');
    $getTwitter_Site = MW_EXT_Kernel::getConfig('EXT_SEO_Twitter_Site');
    $getTwitter_Creator = MW_EXT_Kernel::getConfig('EXT_SEO_Twitter_Creator');
    $getStreetAddress = MW_EXT_Kernel::getConfig('EXT_SEO_StreetAddress');
    $getAddressLocality = MW_EXT_Kernel::getConfig('EXT_SEO_AddressLocality');
    $getAddressRegion = MW_EXT_Kernel::getConfig('EXT_SEO_AddressRegion');
    $getPostalCode = MW_EXT_Kernel::getConfig('EXT_SEO_PostalCode');
    $getAddressCountry = MW_EXT_Kernel::getConfig('EXT_SEO_AddressCountry');
    $getContactType = MW_EXT_Kernel::getConfig('EXT_SEO_ContactType');
    $getArticlePublisher = MW_EXT_Kernel::getConfig('EXT_SEO_ArticlePublisher');

    // Get system info.
    $getDateCreated = DateTime::createFromFormat('YmdHis', MW_EXT_Kernel::getTitle()->getEarliestRevTime());
    $getDateModified = DateTime::createFromFormat('YmdHis', MW_EXT_Kernel::getTitle()->getTouched());
    $getFirstRevision = MW_EXT_Kernel::getTitle()->getFirstRevision();
    $getImage = key($out->getFileSearchOptions());
    $getImageObject = wfFindFile($getImage);
    $getRevision = MW_EXT_Kernel::getWikiPage()->getRevision();
    $getHeadline = MW_EXT_Kernel::outClear(MW_EXT_Kernel::getTitle()->getText());
    $getAltHeadline = $getHeadline;
    $getKeywords = MW_EXT_Kernel::outClear(str_replace('Категория:', '', implode(', ', array_keys(MW_EXT_Kernel::getTitle()->getParentCategories()))));
    $getWordCount = MW_EXT_Kernel::getTitle()->getLength();
    $getArticleURL = $getSiteURL . '/' . '?curid=' . MW_EXT_Kernel::getTitle()->getArticleID();
    $getArticleID = $getSiteURL . '/' . '?curid=' . MW_EXT_Kernel::getTitle()->getArticleID();
    $getExtDescription = $parserOutput->getProperty('description'); // Set by "Description2" extension.

    if ($getExtDescription !== false) {
      $getDescription = $getExtDescription;
    } else {
      $getDescription = '';
    };

    // Get article text.
    if ($getRevision) {
      $getContent = $getRevision->getContent(Revision::FOR_PUBLIC);
      $getContentText = ContentHandler::getContentText($getContent);
      $getArticleText = trim(preg_replace('/\s+/', ' ', strip_tags($getContentText)));
    } else {
      $getArticleText = '';
    }

    $getArticleBody = MW_EXT_Kernel::outClear($getArticleText);

    // Get article created date.
    $getDateCreated = $getDateCreated ? $getDateCreated->format('c') : '0';

    // Get article modified date.
    $getDateModified = $getDateModified ? $getDateModified->format('c') : '0';

    // Get article author.
    if ($getFirstRevision) {
      $getUser = User::newFromId($getFirstRevision->getUser());
      $getUserName = $getFirstRevision->getUserText();
      $getUserGroups = $getUser->getGroups();
      $getAuthorName = MW_EXT_Kernel::outClear($getUserName);
      $getAuthorURL = $getUser->getUserPage()->getFullURL();
      $getAuthorJobTitle = MW_EXT_Kernel::outClear(implode(', ', array_values($getUserGroups)));
    } else {
      $getAuthorName = MW_EXT_Kernel::getConfig('EXT_SEO_AuthorName');
      $getAuthorJobTitle = '';
      $getAuthorURL = '';
    }

    // Get article image.
    if ($getImage && $getImageObject) {
      $getImageURL = $getImageObject->getFullURL();
      $getImageWidth = $getImageObject->getWidth();
      $getImageHeight = $getImageObject->getHeight();
    } else {
      $getImageURL = $getSiteURL . $getSiteLogo;
      $getImageWidth = getimagesize($getImageURL)[0];
      $getImageHeight = getimagesize($getImageURL)[1];
    }

    // -------------------------------------------------------------------------------------------------------------
    // Init JSON-LD.
    // -------------------------------------------------------------------------------------------------------------

    $json = [];

    // Loading JSON-LD.
    $json['@context'] = 'http://schema.org';
    $json['@type'] = 'Article';
    $json['headline'] = $getHeadline;
    $json['alternativeHeadline'] = $getAltHeadline;
    $json['description'] = $getDescription;
    $json['keywords'] = $getKeywords;
    $json['dateCreated'] = $getDateCreated;
    $json['datePublished'] = $getDateCreated;
    $json['dateModified'] = $getDateModified;
    $json['wordCount'] = $getWordCount;
    $json['url'] = $getArticleURL;
    // $json['articleBody']      = $getArticleBody;

    $json['mainEntityOfPage'] = [
      '@type' => 'WebPage',
      '@id' => $getArticleID,
    ];

    $json['author'] = [
      '@type' => 'Person',
      'name' => $getSiteName,
      //'jobTitle' => $getAuthorJobTitle,
      'url' => $getSiteURL,
    ];

    $json['image'] = [
      '@type' => 'ImageObject',
      'url' => $getImageURL,
      'height' => $getImageWidth,
      'width' => $getImageHeight,
    ];

    $json['publisher'] = [
      '@type' => 'Organization',
      'name' => $getSiteName,
      'url' => $getSiteURL,
      'logo' => [
        '@type' => 'ImageObject',
        'url' => $getPublisherLogo,
        'height' => 60,
        'width' => 600,
      ],
      'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => $getStreetAddress,
        'addressLocality' => $getAddressLocality,
        'addressRegion' => $getAddressRegion,
        'postalCode' => $getPostalCode,
        'addressCountry' => $getAddressCountry,
      ],
      'contactPoint' => [
        '@type' => 'ContactPoint',
        'contactType' => $getContactType,
        'telephone' => $getSitePhone,
        'email' => $getSiteEmail,
        'url' => $getSiteURL,
      ],
      'sameAs' => [
        $getURL_Vk,
        $getURL_Facebook,
        $getURL_Twitter,
        $getURL_Discord,
      ]
    ];

    // Render JSON-LD.
    $json_encode = json_encode($json, JSON_UNESCAPED_UNICODE);
    $out->addHeadItem('mw-ext-seo-json', '<script type="application/ld+json">' . $json_encode . '</script>');

    // -------------------------------------------------------------------------------------------------------------
    // HTTP-EQUIV.
    // -------------------------------------------------------------------------------------------------------------

    // Render HTTP-EQUIV.
    $out->addHeadItem('mw-ext-seo-http', '' . Html::element('meta', [
        'http-equiv' => 'X-UA-Compatible',
        'content' => 'IE=edge',
      ]));

    // -------------------------------------------------------------------------------------------------------------
    // DNS prefetch.
    // -------------------------------------------------------------------------------------------------------------

    $dns = [
      '//cdn.jsdelivr.net',
      '//fonts.googleapis.com',
      '//use.fontawesome.com',
    ];

    // Render dns.
    foreach ($dns as $key => $value) {
      if ($value) {
        //$out->addHeadItem( 'mw-ext-seo-dns-' . $key, '<link rel="dns-prefetch" href="' . $value . '"/>' );
      }
    }

    // -------------------------------------------------------------------------------------------------------------
    // Favicon.
    // -------------------------------------------------------------------------------------------------------------

    $out->addHeadItem('mw-ext-seo-favicon', '<link rel="icon" type="image/x-icon" href="' . $getFavicon . '"/>');

    // -------------------------------------------------------------------------------------------------------------
    // Meta.
    // -------------------------------------------------------------------------------------------------------------

    $meta = [];

    // Loading Meta.
    $meta['viewport'] = 'width=device-width, initial-scale=1, maximum-scale=1';
    $meta['keywords'] = $getKeywords;
    $meta['author'] = $getSiteName;
    $meta['designer'] = $getSiteName;
    $meta['publisher'] = $getSiteName;
    $meta['distribution'] = 'web';
    $meta['rating'] = 'general';
    $meta['reply-to'] = $getSiteEmail;
    $meta['copyright'] = $getSiteName;
    $meta['referrer'] = 'strict-origin';
    $meta['theme-color'] = $getThemeColor;
    $meta['msapplication-TileColor'] = $getMSTileColor;

    // Render META.
    foreach ($meta as $name => $value) {
      if ($value) {
        $out->addHeadItem('mw-ext-seo-meta' . $name, '' . Html::element('meta', [
            'name' => $name,
            'content' => $value,
          ]));
      }
    }

    // -------------------------------------------------------------------------------------------------------------
    // Link.
    // -------------------------------------------------------------------------------------------------------------

    $link = [];

    $link['publisher'] = $getPublisher;
    $link['manifest'] = $getManifest;

    // Render link.
    foreach ($link as $rel => $value) {
      if ($value) {
        $out->addHeadItem('mw-ext-seo-rel' . $rel, '' . Html::element('link', [
            'rel' => $rel,
            'href' => $value,
          ]));
      }
    }

    // -------------------------------------------------------------------------------------------------------------
    // Open Graph.
    // -------------------------------------------------------------------------------------------------------------

    // OG type.
    $getType = MW_EXT_Kernel::getTitle()->isMainPage() ? 'website' : 'article';

    $og = [];

    // Loading Open Graph.
    $og['og:type'] = $getType;
    $og['og:site_name'] = $getSiteName;
    $og['og:title'] = $getHeadline;
    $og['og:description'] = $getDescription;
    $og['og:image'] = $getImageURL;
    //$og['og:image:width']         = $getImageWidth;
    //$og['og:image:height']        = $getImageHeight;
    $og['og:url'] = $getArticleURL;
    $og['article:published_time'] = $getDateCreated;
    $og['article:modified_time'] = $getDateModified;
    $og['article:author'] = $getSiteName;
    $og['article:publisher'] = $getArticlePublisher;
    $og['article:tag'] = $getKeywords;
    $og['fb:app_id'] = '';

    // Render Open Graph.
    foreach ($og as $property => $value) {
      if ($value) {
        $out->addHeadItem('mw-ext-seo-og' . $property, '' . Html::element('meta', [
            'property' => $property,
            'content' => $value,
          ]));
      }
    }

    // -------------------------------------------------------------------------------------------------------------
    // Twitter.
    // -------------------------------------------------------------------------------------------------------------

    $twitter = [];

    // Loading Twitter Card.
    $twitter['twitter:card'] = 'summary';
    $twitter['twitter:title'] = $getHeadline;
    $twitter['twitter:description'] = $getDescription;
    $twitter['twitter:image'] = $getImageURL;
    $twitter['twitter:site'] = '@' . $getTwitter_Site;
    $twitter['twitter:creator'] = '@' . $getTwitter_Creator;

    // Render Twitter Card.
    foreach ($twitter as $name => $value) {
      if ($value) {
        $out->addHeadItem('mw-ext-seo-twitter' . $name, '' . Html::element('meta', [
            'name' => $name,
            'content' => $value,
          ]));
      }
    }

    // -------------------------------------------------------------------------------------------------------------
    // DC.
    // -------------------------------------------------------------------------------------------------------------

    $dc = [];

    $dc['DC.Title'] = $getHeadline;
    $dc['DC.Date.Issued'] = $getDateCreated;
    $dc['DC.Date.Created'] = $getDateCreated;

    // Render DC.
    foreach ($dc as $name => $value) {
      if ($value) {
        $out->addHeadItem('mw-ext-seo-dc' . $name, '' . Html::element('meta', [
            'name' => $name,
            'content' => $value,
          ]));
      }
    }

    return true;
  }

  /**
   * Load function.
   *
   * @param OutputPage $out
   * @param ParserOutput $parserOutput
   *
   * @return bool
   * @throws \ConfigException
   * @throws \MWException
   */
  public static function onOutputPageParserOutput(OutputPage $out, ParserOutput $parserOutput)
  {
    if (!MW_EXT_Kernel::getTitle() || !MW_EXT_Kernel::getTitle()->isContentPage() || !MW_EXT_Kernel::getWikiPage()) {
      return null;
    }

    self::onRenderSEO($out, $parserOutput);

    return true;
  }
}
