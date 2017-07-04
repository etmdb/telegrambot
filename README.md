# EtMDB-Telegram Bot

[![N|Solid](https://etmdb.com/static/rest_framework_swagger/images/logo_small.png)](https://etmdb.com/api)

EtMDB is a movie database website tha offers a searchable database for Ethiopian movies, 
Ethiopian actors, actress, directors, writers, production and distribution companies and full movie crews. 
EtMDB provides users with rating and watch-list to improve movie lovers with exclusive contents.

  - Ethiopian Movies and movie metadata (such as Title, Plot, Release Year, Genre, Rating)
  - Movie Ratings
  - Movie Awards
  - Cinemas
  - Film Companies with their credits

# New Features!
  - Cinema schedule
  - Adding movies, artists, listing jobs and articles

### Tech

EtMDB uses a number of open source projects to work properly:

* [Django] - high-level Python Web framework that encourages rapid development and clean, pragmatic design. 
* [Python] - awesome!
* [Django Rest-framework] - powerful and flexible toolkit for building Web APIs
* [Swagger 2.0] - framework of API developer tools for the OpenAPI Specification(OAS)
* [OAuth 2.0] - industry-standard protocol for authorization
* [markdown-it] - Markdown parser done right. Fast and easy to extend.
* [Twitter Bootstrap] - great UI boilerplate for modern web apps
* [jQuery] - duh

And of course EtMDB Bots using our API itself is open source with a [public repository][etmdb] on GitHub.

### Development
Want to contribute? Great! Contact us on developers@etmdb.com or visit our website. 

#### Using the API
```sh
Creating and updating Applications 
Application details: "https://etmdb.com/api/oauth/applications/$APPLICATION-ID/"
Update Application: "https://etmdb.com/api/oauth/applications/$APPLICATION-ID/update/"
Delete Application: "https://etmdb.com/api/oauth/applications/$APPLICATION-ID/delete/"
```
```sh
Access token and refresh token with one to these two methods below
curl -X $HTTP_METHOD -d "grant_type=password&username=$USERNAME&password=$PASSWORD&scope=$SCOPE" -u "$CLIENT-ID:$CLIENT-SECRET-KEY" "https://etmdb.com/api/oauth/token/"
curl -X $HTTP_METHOD -d "grant_type=password&username=$USERNAME&password=$PASSWORD&scope=$SCOPE" -u "https://$CLIENT-ID:$CLIENT-SECRET-KEY@etmdb.com/api/oauth/token/"
```
#### EtMDB API Documentation 

See [EtMDB API](https://etmdb.com/api)

### Todos for the EtMDB-Telegram Bot

 - Create inline telegram bot
 - Searching movies (by title, release year, cast)
 - Searching artists (by name, movies participated in, nick name, character name)
 - Search cinemas (by title)
 - Search film companies (by title and credits)
 - Write documentation

License
----
MIT

**Let's Write our Movie History Together!**

[//]: # (These are reference links used in the body of this note and get stripped out when the markdown processor does its job. There is no need to format nicely because it shouldn't be seen. Thanks SO - http://stackoverflow.com/questions/4823468/store-comments-in-markdown-syntax)


   [etmdb]: <https://github.com/etmdb>
   [dawit-git-url]: <https://github.com/dawitnida>
   [Dawit Nida]: <http://dawitnida.com>
   [OAuth 2.0]: <https://oauth.net/2/>
   [markdown-it]: <https://github.com/markdown-it/markdown-it>
   [Django]: <https://www.djangoproject.com/>
   [Django Rest-framework]: <http://www.django-rest-framework.org/>
   [Twitter Bootstrap]: <http://twitter.github.com/bootstrap/>
   [jQuery]: <http://jquery.com>
   [@tjholowaychuk]: <http://twitter.com/tjholowaychuk>
   [Swagger 2.0]: <https://swagger.io/>
   [AngularJS]: <https://angularjs.org>
   [Python]: <https://www.python.org/>
