# PURR-Pivot-Prototype (PURRPP)
Mockup of harvesting, search and register of datasets.
The purpose of this software is to explore and test new workflows for search and registration.

# Requirements

* PHP > 7.1
* Elasticsearch > 6 or docker

# Setup

Install dependencies:

`composer install`

Start elasticsearch:
Download and run https://www.elastic.co/downloads/elasticsearch 
Or run via docker: 

`docker run -p 9200:9200 -e "discovery.type=single-node" docker.elastic.co/elasticsearch/elasticsearch`

Start development server:

`php bin/console server:start`

# Import to elasticsearch
To harvest a oai-pmh endpoint run:

`php bin/console app:harvest https://purr.purdue.edu/oaipmh`

# TODO
- [x] Autocomplete api for registration form (subject & creator)
- [ ] Call autocomplete on search form 
- [ ] Date range filter
- [ ] Clickable filters in sidebar
- [ ] Landing pages for dataserts registred in the portal
- [ ] Search result pagination