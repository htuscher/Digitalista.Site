# Digitalista.de Neos Site Package

This contains all functionality of the news page http://digitalista.de.
Sorry for not seperating the functionalities, but that was my first big Neos project that I built in my spare time.
The News node handling was heavily inspired by the News package from Weissheiten https://github.com/Weissheiten/Weissheiten.Neos.News.

## Neos Route configuration

        #                                                                        #
        # Routes configuration                                                   #
        #                                                                        #
        # This file contains the configuration for the MVC router.               #
        # Just add your own modifications as necessary.                          #
        #                                                                        #
        # Please refer to the Flow manual for possible configuration options.    #
        #                                                                        #
        
        ##
        # TYPO3 Neos subroutes
        -
          name: 'Onedrop - Ajax Form'
          uriPattern: 'form/{formIdentifier}/{presetName}'
          defaults:
            '@package': 'Onedrop.AjaxForm'
            '@controller': 'AjaxForm'
            '@action': 'index'
            '@format': 'html'
          httpMethods: ['GET','POST']
        -
          name: 'Jhoechtl.SearchPlugin'
          uriPattern: '<SearchSubroutes>'
          subRoutes:
            'SearchSubroutes':
              package: 'Jhoechtl.SearchPlugin'
        -
          name:  'Google Sitemap'
          uriPattern: '{node}sitemap.xml'
          defaults:
            '@package':    'TYPO3.Neos'
            '@controller': 'Frontend\Node'
            '@action':     'show'
            '@format':     'xml'
          routeParts:
            'node':
              handler: 'TYPO3\Neos\Routing\FrontendNodeRoutePartHandlerInterface'
              options:
                onlyMatchSiteNodes: TRUE
        -
          name:  'Category RSS Feed'
          uriPattern: '{node}/rss.xml'
          defaults:
            '@package':    'TYPO3.Neos'
            '@controller': 'Frontend\Node'
            '@action':     'show'
            '@format':     'rss'
          routeParts:
            'node':
              handler: 'TYPO3\Neos\Routing\FrontendNodeRoutePartHandlerInterface'
              options:
                onlyMatchSiteNodes: FALSE
        -
          name:  'Juicebox Gallery'
          uriPattern: '{node}/config.xml'
          defaults:
            '@package':    'TYPO3.Neos'
            '@controller': 'Frontend\Node'
            '@action':     'show'
            '@format':     'xml'
          routeParts:
            'node':
              handler: 'TYPO3\Neos\Routing\FrontendNodeRoutePartHandlerInterface'
              options:
                onlyMatchSiteNodes: FALSE
        -
          name: 'News paginate'
          uriPattern:    '{node}/seite-{--newsList.currentPage}.html'
          defaults:
            '@package':    'TYPO3.Neos'
            '@controller': 'Frontend\Node'
            '@format':     'html'
            '@action':     'show'
            '--newsList':
              '@package': ''
              '@subpackage': ''
              '@controller': ''
              '@action': 'index'
              'currentPage': '1'
          routeParts:
            node:
              handler: TYPO3\Neos\Routing\FrontendNodeRoutePartHandler
          appendExceedingArguments: TRUE
        -
          name: 'AMP HTML'
          uriPattern:    '{node}.amp.html'
          defaults:
            '@package':    'TYPO3.Neos'
            '@controller': 'Frontend\Node'
            '@format':     'html'
            '@action':     'show'
            'amp': '1'
          routeParts:
            node:
              handler: TYPO3\Neos\Routing\FrontendNodeRoutePartHandler
          appendExceedingArguments: TRUE
        -
          name: 'TYPO3 Neos'
          uriPattern: '<TYPO3NeosSubroutes>'
          subRoutes:
            'TYPO3NeosSubroutes':
              package: 'TYPO3.Neos'
              variables:
                'defaultUriSuffix': '.html'


## Features inside this package

Beyond there's a list of useful features that I developed for the news page to be easily maintainable.
You might find some useful things for yourself and I would love to get other people to help me on this page.

If you're interested, contact me or just give me a PR.

### PageView tracking and listing

As all page views in Neos by the `TYPO3\Neos\Controller\Frontend\NodeController->showAction()` I had the idea of 
using an aspect before that action to store page views as a model.
You shouldn't to this as it doesn't work if you have a caching proxy infront of your page.
Use Google Analytics or Piwik instead and build yourself an ajax controller in Flow.

**Related files:**

* `Classes/Jhoechtl/Digitalista/Aspect/PageViewAspect.php`
* `Classes/Jhoechtl/Digitalista/Domain/Model/PageView.php`
* `Classes/Jhoechtl/Digitalista/Domain/Repository/PageViewRepository.php`
* `Classes/Jhoechtl/Digitalista/Controller/PageViewController.php`
* `Classes/Jhoechtl/Digitalista/Controller/PageViewController.php`
* `Configuration/NodeTypes.pageViews.yaml`
* `Configuration/Policy.yaml`
* `Resources/Private/TypoScript/NodeTypes/PageViews.ts2`
* `Resources/Private/Templates/PageView/Views.html`

### Automatic news moving on node publishing

As the site structure contains nodes that aggregate news of a year, month and day it's quite hard for an editor
to create his article in the right place. You need to create node for the day if it doesn't exist etc.
So I introduced a special "draft" area having only the upper category nodes as duplicates and if an article is 
published, it's node is moved and the parent nodes are auto-created.

**Related files:**

* `Classes/Jhoechtl/Digitalista/Package.php`
* `Classes/Jhoechtl/Digitalista/Service/ArticlePublisher.php`
* `Classes/Jhoechtl/Digitalista/Command/IntegrityCommandController.php`

### Google AMP

Accelerated mobile pages is a trend coming up which provides a specialized mobile version of your site so that
Google can serve the content entirely from their cache.
I built this detecting mobile devices and then redirecting to the AMP version of the page.
Every NodeType is supposed to have a special template for the AMP version (if possible).
This feature is currently deactivated as I never finished building all NodeType templates for AMP.

**Related files:**

* `Classes/Jhoechtl/Digitalista/Aspect/AmpMobileAspect.php`
* `Resources/Private/TypoScript/Amp.ts2`

### Read facebook and twitter share counts (Generate node URLs in CLI context)

I introduced a `SharesCommandController` that generates the public URLs for every News node, reads the share counts
from Twitter and Facebook and stores them as node properties inside the node to be displayed in the frontend.
It would also be possible to flush the cache if a node is updated, but I didn't implement that.

**Related files:**

* `Classes/Jhoechtl/Digitalista/Command/SharesCommandController.php`
* `Classes/Jhoechtl/Digitalista/Service/SocialSharesService.php`
* `Configuration/NodeTypes.news.yaml`

### Sliding content collection

The right sidebar of the page slides up the node-tree until it finds a document node having at least one node inside
the sidebar collection. I added an EEL helper for this to have an easy function like 
`${Digitalista.Node.nonEmptyPathCollection(node, this.nodePath)}` to get the right ContentCollection node.
The cache clearing is currently a bit unspecific. It would be better to have a cache tag that stores the node
from which the collection is served.

    sidebar = ContentCollection {
        nodePath = 'sidebar'
        @override.node = ${Digitalista.Node.nonEmptyPathCollection(node, this.nodePath)}
        @cache.entryTags.1 = 'Everything'
    }

**Related files:**

* `Classes/Jhoechtl/Digitalista/TypoScript/Helper/NodeHelper.php`