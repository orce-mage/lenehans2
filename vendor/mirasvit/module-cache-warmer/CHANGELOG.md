# Change Log
## 1.6.1
*(2021-07-30)*

#### Fixed
* ChartJs lib for Magento < 2.4.0
* composer requirenments updated

---


## 1.6.0
*(2021-07-15)*

#### Improvements
* Updated statistics charts (small screen)

---

## 1.5.9
*(2021-07-13)*

#### Improvements
* Updated statistics charts

---


## 1.5.8
*(2021-07-02)*

#### Features
* Warming pages variations mode

---


## 1.5.7
*(2021-06-15)*

#### Fixed
* Update default source on customer group save/delete

---


## 1.5.6
*(2021-05-20)*

#### Improvements
* Set crawler source for pages while the CLI crawler is running if the source with the type 'crawler' have been created and active

#### Fixed
* Fixed the issue with negative values in the efficiency report

---


## 1.5.5
*(2021-05-05)*

#### Fixed
* Hide test pages from search engines

---


## 1.5.4
*(2021-04-27)*

#### Improvements
* Small improvement in lock manager

---


## 1.5.3
*(2021-04-21)*

#### Fixed
* Fixed the issue with locks (Magento 2.3.*)

---


## 1.5.2
*(2021-04-06)*

#### Improvements
* Time threshold for ignored pages cleanup (cron)

---


## 1.5.1
*(2021-04-05)*

#### Improvements
* Lock Manager
* Cron jobs optimization

---


## 1.5.0
*(2021-03-23)*

#### Fixed
* Issue with the filter by Warm Rules in the Pages grid

---


## 1.4.9
*(2021-03-18)*

#### Fixed
* Fixed the issue with inactive rules in the Pages grid

---


## 1.4.8
*(2021-02-26)*

#### Improvements
* Update pages by Warm Rules improved [#284]()

---


## 1.4.7
*(2021-02-11)*

#### Fixed
* Fixed the issue related to URLs not added from the file source in some cases [#282]()

---


## 1.4.6
*(2021-02-05)*

#### Improvements
* Ability to parse segmented sitemap [#279]()

---


## 1.4.5
*(2021-02-04)*

#### Features
* URL sources (<a href="https://mirasvit.com/docs/module-cache-warmer/current/configuration/sources">Check here</a>)

---


## 1.4.4
*(2021-01-22)*

#### Improvements
* Predefined performance levels [#266]()
* Performance [#265]()

---


## 1.4.3
*(2020-12-24)*

#### Fixed
* Fixed the issue with warming pages (affects from 1.4.1) ([#263]())

---


## 1.4.2
*(2020-12-23)*

#### Fixed
* Compatibility check issue fixed [#261]()

---


## 1.4.1
*(2020-12-21)*

#### Improvements
* Database queries execution improved ([#259]())
* Ignored pages cleanup performance improved ([#258]())

---


## 1.4.0
*(2020-12-09)*

#### Improvements
* Cleanup cron improved [#256]()

#### Documentation
* faq - Check FPC

---


## 1.3.37
*(2020-11-09)*

#### Fixed
* Small fix ([#254]())

---


## 1.3.36
*(2020-11-05)*

#### Fixed
* Fixed potential performance issue ([#252]())

---


## 1.3.35
*(2020-11-03)*

#### Fix
* Fixed XSS issue ([#249]())

---


## 1.3.34
*(2020-11-03)*

#### Improvements
* Cache type in the debug toolbar and Redis displayed if used ([#247]())

---


## 1.3.33
*(2020-10-28)*

#### Fixed
* Fixed issue with traces (Magento 2.4) ([#245]())

---


## 1.3.32
*(2020-10-28)*

#### Fixed
* Test page issue ([#243]())

---


## 1.3.31
*(2020-10-20)*

#### Improvements
* Cache flushes page improved ([#241]())

---

## 1.3.30
*(2020-09-25)*

#### Improvements
* Test page improved ([237]())
* Page status checking for Varnish improved ([236]())
* Debug popup improvement ([#235]())

---


## 1.3.29
*(2020-08-18)*

#### Fixed
* Fixed issue with page statuses in the toolbar in Safari browser ([#228]())

---

## 1.3.28
*(2020-08-12)*

#### Fixed
* Keep log data for last 3 month ([#226]())

---


## 1.3.27
*(2020-07-29)*

#### Improvements
* Support of Magento 2.4

---


## 1.3.26
*(2020-06-16)*

#### Fixed
* Issue with clear cache for CMS pages from admin panel (Varnish)

---

## 1.3.25
*(2020-03-19)*

#### Fixed
* Issue with saving ignored page types after all page types unselected (affects only 1.3.24)

---

## 1.3.24
*(2020-03-12)*

#### Fixed
* Issue with deleting pages by mst_cache_warmer_cleanup job from the database when prefix for table name present.
(SQLSTATE[42S02]: Base table or view not found, affects from 1.3.18)

#### Features
* Ignored page types
---

## 1.3.23
*(2020-02-24)*

#### Fixed
* Issue with saving hole punch config and Forcibly make pages cacheable by page types in Magento version 2.3.2 and higher

---

## 1.3.22
*(2020-01-24)*

#### Fixed
* Issue with editing warm rules in Magento 2.1.* (Notice: unserialize(): Error at offset ...; affects from 1.3.12)

---

## 1.3.21
*(2020-01-20)*

#### Features
* Ability to disable collecting statistics (disable /cache_warmer/track request)

---

## 1.3.19
*(2019-11-29)*

#### Fixed
* Issue with no page types in fresh installation

---


## 1.3.18
*(2019-11-12)*

#### Improvements
* error logging in the job trace ([#197]())

#### Fixed
* Issue with asymmetric transaction rollback
* Issue with PHP out-of-memory error

---


## 1.3.17
*(2019-11-01)*

#### Fixed
* Issue with checkout in Safari 7.* and older

---


## 1.3.16
*(2019-10-21)*

#### Fixed
* Issue with server load rate
* Issue with Asymmetric transaction rollback

---


## 1.3.15
*(2019-09-19)*

#### Fixed
* Prevent removing duplicates when the pages table is empty
* Prevent decode attachment link as page ([#178]())

---


## 1.3.14
*(2019-08-22)*

#### Fixed
* issue with server load rate
* issue with expressions
* Issue with update

---


## 1.3.13
*(2019-08-19)*

#### Fixed
* Issue with saving warm rule

---


## 1.3.12
*(2019-08-15)*

#### Fixed
* Minor changes

---


## 1.3.11
*(2019-06-13)*

#### Features
* Improve SQL queries performance ([#170]())
* Ability to completely disable toolbar ajax requests ([#172]())

#### Fixed
* remove pages which return 404/301/302 ([#174]())

---


## 1.3.10
*(2019-05-03)*

#### Fixed
* Crawl command was not able to correctly process URLs without base path

---



## 1.3.9
*(2019-05-02)*

#### Features
* Add ignore by pattern notification to the dev toolbar

#### Fixed
* Issue during data migration from old versions

---


## 1.3.7
*(2019-04-24)*

#### Fixed
* Warming issue in version 1.3.6

---


## 1.3.6
*(2019-04-22)*

#### Fixed
* Moved setup data migration in backgroud by cron

---


## 1.3.5
*(2019-04-18)*

#### Fixed
* Possible errors: Page already exists. Unable to save a duplicate…

---


## 1.3.4
*(2019-04-16)*

#### Fixed
* Fixed possible issues during extension upgrade from old versions

---


## 1.3.3
*(2019-04-12)*

#### Fixed
* Status update must use priorities and scope defined by rules

---


## 1.3.2
*(2019-04-09)*

#### Improvements
* Show error message during crawling if console crawler cant open URL

#### Fixed
* Unable to upgrade because of very long URLs (since version 1.3.0)

---

### 1.3.1
*(2019-04-02)* 

#### Features
* Improved monitoring of cache status
* Removed vary data modificator from warming rules
* Different minor fixes and changes

### 1.2.18
*(2019-03-27)* 

#### Features
* Added crawling for logged-in customers

#### Fixed
* Fixed some conflicts with 3rd party plugins

## 1.2.16
*(2019-03-11)*

#### Features
* Remove Google gclid from URLs
* Allow crawling for pages without varydata, on varnish, non-default stores
---


## 1.2.15
*(2019-02-28)*

#### Fixed
* M2.1, PHP5.6, error "Cannot use Symfony\Component\Console\Command\Command as Command because the name is already in use"
* Issue with Could not resolve host on some server configurations

---


## 1.2.13
*(2019-02-07)*

#### Fixed
* reduce log of cache flushes
* incorrect X-Magento-Cache-Debug header
* URL duplicates
* Notice: unserialize(): Error at offset ... Config/ExtendedConfig.php
* Issue with compilation without database

---


## 1.2.12
*(2019-01-08)*

#### Improvements
* Remove URLs which fail with errors few times

#### Fixed
* In production mode, we don't see cache flushed message
* Console bin/magento mirasvit:cache-warmer:test failed with error

---


## 1.2.10
*(2018-12-18)*

#### Improvements
* Cache fill rate chart

#### Fixed
* Hole punch didn't work if template is located in non-default theme
* Warm rules does not apply filter by store. Clean of mst_cache_warmer_page is required for fix.
* Solved possible out of memory errors during logs clean up by cron
* Possible issue with incorrect cache headers of ajax requests
* If store use direct IP, warmer does not add pages to the queue
* Possible problem with SSL certificate in some cases

---


## 1.2.8
*(2018-12-05)*

#### Fixed
* Error during log cleanup (affects only 1.2.7, 1.2.6)
* In debug mode, warmer adds debug html to non-html pages (like /robots.txt)

---



### 1.2.7
*(2018-12-04)* 

#### Improvements
* Support of Magento 2.3

#### Fixed
* In some cases, extension can broke ajax calls of 3rd party extensions
* Warming rules for customer groups
* Improved clean of trace logs

---


## 1.2.5
*(2018-11-29)*

#### Fixed
* Issue with rendering admin for M 2.1
* Magento 2.1, error Class Magento\Framework\Serialize\Serializer\Json does not exist (affects from 1.2.0)

---


## 1.2.4
*(2018-11-28)*

#### Improvements
* Changed condition for remove old logs

#### Fixed
* Issue with adding pages to list

---


## 1.2.3
*(2018-11-23)*

#### Fixed
* Error if I run crawl via SSH. Affects only 1.2.2

---


## 1.2.2
*(2018-11-22)*

#### Fixed
* When I try to warm cache via admin, I receive error: "Argument 2 passed to Mirasvit\CacheWarmer\Service\WarmerService::warmCollection()..". Affects only 1.2.0.

---


## 1.2.0
*(2018-11-09)*

#### Features
* Cache clean trace
* Warm Rules (Priority System)

#### Improvements
* Show crawler error if customer has http autorization


#### Fixed
* Incorrect calculation of cache hits rate
* M2.1 some pages are not warmed
* If our toolbar is enabled and file robots.txt is missing in the customer's store, we incorrectly added some html tags to request by URI /robots.txt.
* 301 as error in log

---


## 1.1.62
*(2018-10-29)*

#### Fixed
* **Issue with stored serialized vary_data**<br>
   Warmer can't correctly warm URLs which were added to the queue.<br>
   
---


## 1.1.61
*(2018-10-26)*

#### Improvements
* CURL for pages

---


## 1.1.60
*(2018-10-25)*

#### Improvements
* CURL information to trace
* Skip warming, if page cache is disabled

---


## 1.1.59
*(2018-10-24)*

#### Improvements
* Added ability use with HTTP auth

#### Fixed
* Issue with compilation

---


## 1.1.58
*(2018-10-24)*

#### Improvements
* Bolt FPC compatibility

---


## 1.1.57
*(2018-10-24)*

#### Improvements
* Remove page only after 3 unsuccessful warm attempts

---


## 1.1.56
*(2018-10-22)*

#### Fixed
* Unserialize issue

---


## 1.1.55
*(2018-10-17)*

#### Improvements
* Charts
* Cache clean logger

---


## 1.1.53
*(2018-10-11)*

#### Improvements
* Refactoring
* Trace

#### Fixed
* Time issue in trace
* Crawler does not follows redirects

---

## 1.1.52
*(2018-09-24)*

#### Fixed
* Fixed out of memory crashes

#### Improvements
* Added option "Ignored User-agents"
* Stop job execution after 3 errors 

---


## 1.1.51
*(2018-08-31)*

#### Improvements
* Clear old logs by cron (30 days)

---


## 1.1.50
*(2018-08-30)*

#### Fixed
* Slightly improved TTFB

---


## 1.1.49
*(2018-08-30)*
#### Improvements
* Use the same cache for new visitor

---

## 1.1.48
*(2018-08-27)*
#### Fixed
* Fixed and error: "Read timed out after 60 seconds..." (for some stores)

---

## 1.1.47
*(2018-08-27)*

#### Improvements
* Coverage rate

#### Fixed
* Fixed "Invalid URI supplied" error (for urls more than 255 symbols)
* Fixed rate limit depending from "Crawler limit"
* Fixed and error: "Read timed out after 60 seconds {"exception":"[object] (Zend_Http_Client_Adapter_Exception(code: 1000): Read timed out after 60 seconds at .../vendor/magento/zendframework1/library/Zend/Http/Client/Adapter/Socket.php" (for some stores)

---

## 1.1.46
*(2018-08-20)*

#### Features
* Ability show cache status for Varnish
* Fixed popularity for Varnish

#### Fixed
* Info about extensions which can broke Magento Page Cache

---

## 1.1.45
*(2018-08-13)*

#### Improvements
* Info about extensions which can broke Magento Page Cache

---

## 1.1.44
*(2018-08-03)*

#### Fixed
* Fixed an error: "Unable to Connect to ssl:..."
* Fixed an error if FPC Hole Punch is enabled for widget

---

## 1.1.43
*(2018-07-26)*

#### Fixed
* Fixed an error during compilation

---

## 1.1.42
*(2018-07-26)*

#### Fixed
* Fixed an issue with "Flush Magento Cache" from admin panel if "Forbid cache flushing" set to "Yes"

#### Improvements
* Ability set protocol in cli warmer
* Forbid return from cache an empty page and add in cache an empty page

---

## 1.1.41
*(2018-07-24)*

#### Fixed
* Fixed cli warmer if option "Warm mobile pages separately" is enabled

---

## 1.1.40
*(2018-07-13)*

#### Fixed
* Fixed minor bug (for some stores)

---

## 1.1.39
*(2018-07-13)*

#### Fixed
* Fixed issue with page type disappearing

---

## 1.1.38
*(2018-07-12)*

#### Fixed
* Fixed crawler if "Add Store Code to Urls" set to "Yes"

---


## 1.1.37
*(2018-06-28)*

#### Improvements
* Make pages cacheable by page type

---


## 1.1.36
*(2018-06-23)*

#### Improvements
* Warm pages by page type order

---

## 1.1.35
*(2018-06-23)*

#### Fixed
* Fixed crawling for urls without domains

---

## 1.1.34
*(2018-06-15)*

#### Improvements
* Use empty vary data for mobile pages

---

## 1.1.33
*(2018-06-14)*

#### Features
* Ability warm mobile pages separately

---

## 1.1.32
*(2018-06-13)*

#### Improvements
* Ability prewarm stores depending from currency

---

## 1.1.31
*(2018-06-11)*

#### Fixed
* Delete old(not used) cli options

---

## 1.1.30
*(2018-06-11)*

#### Fixed
* Delete old(not used) cli options

#### Improvements
* TTL info

---

## 1.1.29
*(2018-05-18)*

#### Fixed
* Small css fix

---

## 1.1.28
*(2018-05-08)*

#### Fixed
* Ability clear cache by url for products and categories in Pages listing if Varnish installed

---

## 1.1.27
*(2018-05-07)*

#### Fixed
* bug: Fixed large jobs list with status "Scheduled"

---

## 1.1.26
*(2018-05-03)*

#### Fixed
* Fixed ability run a lot of warm processes

---


## 1.1.25
*(2018-05-02)*

#### Fixed
* Fixed parse error

---


## 1.1.24
*(2018-05-02)*

#### Fixed
* Fixed an error: "Undefined property in .../vendor/mirasvit/module-cache-warmer/src/CacheWarmer/Service/WarmerService.php on line 128"

---

## 1.1.23
*(2018-04-26)*

#### Fixed
* Fixed "Forbid Cache Flushing" option for Varnish

---

## 1.1.22
*(2018-04-25)*

#### Fixed
* Fixed presence not cacheable pages in list

---

## 1.1.21
*(2018-04-20)*

#### Features
* Forcibly make pages cacheable

---

## 1.1.20
*(2018-04-20)*

#### Fixed
* bug: Fixed an error: "Warning: strpos(): Empty needle in .../app/code/Mirasvit/CacheWarmer/Service/BlockMarkService.php on line 108" if "FPC hole punch" enabled without "Template" or "Block class" fields
* Fixed compilation error "Errors during compilation: Mirasvit\CacheWarmer\Service\DebugService..."
* Use also secure base url in cli warmer

#### Improvements
* The same version for Magento 2.1 and Magento 2.2

---

## 1.1.19
*(2018-04-11)*

#### Fixed
* Fixed multi store crawling (from cli)

---

## 1.1.18
*(2018-04-05)*

#### Fixed
* Fixed error while feed generation

---

## 1.1.17
*(2018-04-04)*


#### Improvements
* Ability crawl incorrect html content from command line

#### Fixed
* Fixed re-crawling links

---

## 1.1.16
*(2018-03-23)*

#### Improvements
* Run warmer as web server user

---

## 1.1.15
*(2018-03-22)*

#### Improvements
* Crawl speed improvement

---

## 1.1.14
*(2018-03-07)*

#### Fixed
* Fixed incorrect X-Magento-Vary

---

## 1.1.13
*(2018-03-05)*

#### Fixed
* Cannot instantiate abstract class

---

## 1.1.12
*(2018-03-02)*

#### Improvements
* Automatically using "Don't verify peer" function

#### Fixed
* Fixed compatibility with varnish and fastly
* Ability run setup:di:compile without database

---


## 1.1.11
*(2018-02-22)*

#### Fixed
* Fixed an error "PHP Fatal error:  Class 'Mirasvit\Report\Model\Query\Column\Date\Range' not found in .../CacheWarmer/Reports/Query/Column/Date/Range.php on line 19"

---

## 1.1.10
*(2018-02-22)*

#### Improvements
* Switched to new module-report version

---

## 1.1.9
*(2018-02-12)*

#### Fixed
* Fixed an error "Notice: unserialize(): Error at offset 255 of 255 bytes in .../app/code/Mirasvit/CacheWarmer/Model/Job.php on line 66"

---

## 1.1.8
*(2018-02-02)*

#### Fixed
* Fixed an error "sh: sysctl: command not found"

---

## 1.1.7
*(2018-01-29)*

#### Fixed
* Fixed notice (Notice: Undefined property: Mirasvit\\CacheWarmer\\Plugin\\Debug\\OnMissPlugin::$request in ...vendor\/mirasvit\/module-cache-warmer\/src\/CacheWarmer\/Plugin\/Debug\/OnMissPlugin.php on line 96)

---

## 1.1.6
*(2018-01-23)*

#### Fixed
* Fixed Magento error "(InvalidArgumentException): Unable to serialize value." when incorrect content and json_encode return false

---


## 1.1.5
*(2018-01-19)*

#### Fixed
* Fixed an issue with block excluding when template have module class different from block

---

## 1.1.4
*(2018-01-18)*

#### Fixed
* Fixed minor bug in text
* Fixed the error appeared while using command "unlock" in cli
[Exception]  Warning: unlink(cache-warmer.cli.crawl.lock):  No such file or directory in .../app/code/Mirasvit/Cach
  eWarmer/Console/Command/CrawlCommand.php on line 217

---

### 1.1.3
*(2018-01-05)*

#### Fixed
bug: Fixed incorrect message (for magento 2.2.*)

---

### 1.1.2
*(2018-01-04)*

#### Improvements
improve: Message about incorrect version (if extension installed without composer)

---

### 1.1.1
*(2017-12-08)*

#### Fixed
* Fixed cli error if sysctl command not found

---

### 1.1.0
*(2017-12-07)*

#### Fixed
* Fixed cli command error

#### Documentation
* Documentation improvement

---

## 1.0.63
*(2018-04-05)*

#### Fixed
* Fixed error while feed generation
* Fixed re-crawling links

---

## 1.0.62
*(2018-03-29)*

#### Improvements
* Switched to new module-report version

---

## 1.0.61
*(2018-03-07)*

#### Fixed
* Fixed incorrect X-Magento-Vary

---

## 1.0.60
*(2018-03-02)*

#### Improvements
* Automatically using "Don't verify peer" function

#### Fixed
* Ability run setup:di:compile without database

---


## 1.0.59
*(2018-02-12)*

#### Fixed
* Fixed an error "Notice: unserialize(): Error at offset 255 of 255 bytes in .../app/code/Mirasvit/CacheWarmer/Model/Job.php on line 66"

---

## 1.0.58
*(2018-02-02)*

#### Fixed
* Fixed an error "sh: sysctl: command not found"

---

## 1.0.57
*(2018-02-02)*

#### Fixed
* Fixed an error "sh: sysctl: command not found"

---

## 1.0.56
*(2018-01-19)*

#### Fixed
* Fixed an issue with block excluding when template have module class different from block

---

## 1.0.55
*(2018-01-18)*

#### Fixed
* Fixed minor bug in text
* Fixed the error appeared while using command "unlock" in cli
[Exception]  Warning: unlink(cache-warmer.cli.crawl.lock):  No such file or directory in .../app/code/Mirasvit/Cach
  eWarmer/Console/Command/CrawlCommand.php on line 217

---

## 1.0.54
*(2017-12-08)*

#### Fixed
* Fixed cli error if sysctl command not found

---

## 1.0.53
*(2017-12-07)*

#### Fixed
* Fixed cli command error

#### Documentation
* Documentation improvement

---

### 1.0.52
*(2017-11-29)*

#### Fixed
* Fixed cli error

---

### 1.0.51
*(2017-11-29)*

#### Fixed
* Fixed cli error

---

### 1.0.50
*(2017-11-29)*

#### Fixed
* Magento 2.2 compatibility

---

### 1.0.49
*(2017-11-28)*

#### Fixed
* Magento 2.1 compatibility

---

### 1.0.48
*(2017-11-28)*

#### Improvements
* LiteMage compatibility

---

### 1.0.47
*(2017-11-27)*

#### Fixed
* Fixed admin load time issue.

---

### 1.0.46
*(2017-11-24)*

#### Fixed
* Extended config recurring update

---

### 1.0.45
*(2017-11-21)*

#### Improvements
* Refactoring

#### Fixed
* Recurring json update

---

### 1.0.44
*(2017-11-17)*

#### Fixed
* Fix an error in console if use setup:install
* Fixed an error when get cpu count

---

### 1.0.43
*(2017-11-07)*

#### Fixed
* Minor stability fix

---

### 1.0.42
*(2017-11-03)*

#### Improvements
* Stability improvement

#### Fixed
* Fixed an error "Cache frontend 'default' is not recognized." (for some stores)

#### Documentation
* Documantation update

---

### 1.0.41
*(2017-10-18)*

#### Fixed
* Magento 2.2 compatibility

---

### 1.0.40
*(2017-10-17)*

#### Features
* Hole punching for blocks

---

### 1.0.39
*(2017-09-28)*

#### Fixed
* Magento 2.2 compatibility

#### Documentation
* Documentation improvement

---

### 1.0.38
*(2017-08-09)*

#### Fixed
* Fixed an error with comman warm in command line

---

### 1.0.37
*(2017-08-04)*

#### Documentation
* Documentation update

---

### 1.0.36
*(2017-08-04)*

#### Fixed
* Minor compatibility adjustments introduced

#### Documentation
* Documentation update

---

### 1.0.35
*(2017-07-26)*

#### Fixed
* Minor compatibility adjustments introduced

---

### 1.0.34
*(2017-07-24)*

#### Improvements
* Menu improvement

#### Fixed
* Fixed an error

---

### 1.0.33
*(2017-07-24)*

#### Improvements
* Refactoring

---

### 1.0.32
*(2017-07-18)*

#### Fixed
* Fixed an error

---

### 1.0.31
*(2017-07-17)*

#### Fixed
* Fixed an error "Area code is already set"

---

### 1.0.30
*(2017-07-10)*

#### Fixed
* Fixed an error

---

### 1.0.29
*(2017-07-10)*

#### Fixed
* Fixed an error

---

### 1.0.28
*(2017-07-07)*

#### Improvements
* Ability crawl particular store

#### Fixed
* Fixed issue with maximum job run time
* Compatibility with TemplateMonster

---

### 1.0.27
*(2017-06-27)*

#### Fixed
* Fixed popularity calculation for internal requests

---

### 1.0.26
*(2017-05-15)*

#### Documentation
* Documentation update

---

### 1.0.25
*(2017-05-15)*

#### Improvements
* Report period filter

---

### 1.0.24
*(2017-04-25)*

#### Fixed
* Fixed ability enable report for custom user roles

---

### 1.0.23
*(2017-04-24)*

#### Features
* Reports

---

### 1.0.22
*(2017-04-18)*

#### Improvements
* Ability to run/remove warmer jobs manually

#### Fixed
* Issue with vary string

---

### 1.0.21
*(2017-04-13)*

#### Improvements
* Added lock file, that not allows to run parallel warmer processes
* Replaced product/category observer to plugins

---

### 1.0.20
*(2017-04-12)*

#### Improvements
* Speed up popularity logging

---

### 1.0.19
*(2017-03-24)*

#### Fixed
* Fixed an issue with compilation

---

### 1.0.18
*(2017-03-24)*

#### Improvements
* Performance of fill rate feature

---

### 1.0.17
*(2017-03-23)*

#### Improvements
* Crontab & User Interface

#### Fixed
* Fixed an issue with fill report

---

### 1.0.16
*(2017-03-21)*

#### Improvements
* Added mass actions to grid with pages

#### Fixed
* Fixed an issue with warming by page type

---

### 1.0.15
*(2017-03-15)*

#### Improvements
* Changed default configuration
* Automatically removing not valid pages from warmer list

#### Fixed
* Fixed an issue with checking cache status for Varnish

---

### 1.0.14
*(2017-02-22)*

#### Improvements
* Added console command for test warming features

---

### 1.0.13
*(2017-02-01)*

#### Fixed
* Fixed compilation issue

---

### 1.0.12
*(2017-01-27)*

#### Fixed
* Added compatibility with old SEO version

---

### 1.0.11
*(2017-01-26)*

#### Fixed
* Fixed security issue

---

### 1.0.10
*(2017-01-25)*

#### Fixed
* Fixed an issue with observer event

#### Improvements
* Warm jobs

---

### 1.0.8
*(2017-01-10)*

#### Improvements
* Disable information toolbar for ajax requests (JSON output)

---

### 1.0.7
*(2016-12-29)*

#### Fixed
* Fixed an error if Cache-Control object not exist

---

### 1.0.6
*(2016-12-28)*

#### Documentation
* Improvement

---

### 1.0.5
*(2016-12-28)*

#### Improvements
* Compatibility with M2.2

#### Documentation
* Added new docs

---

### 1.0.4
*(2016-12-16)*

#### Fixed
* Fixed an issue with 404 pages error during crawling (CLI)

#### Documentation
* Settings

---

### 1.0.3
*(2016-12-13)*

#### Improvements
* Updated information and appearance of info block [Screenshot](http://prntscr.com/ditkwc)

---

### 1.0.2
*(2016-11-24)*

#### Improvements
* Compatibility with SEO version 1.0.34

---

### 1.0.1
*(2016-11-07)*

#### Features
* Info block which help check if page in FPC cache

---
