resolver
========

[![Build Status](https://secure.travis-ci.org/pixelpolishers/resolver.png?branch=develop)](http://travis-ci.org/pixelpolishers/resolver)
[![NPM version](https://badge.fury.io/js/pixelpolishers-resolver.png)](http://badge.fury.io/js/pixelpolishers-resolver)

Resolver is a C++ dependency manager and makefile generator. When using Resolver one does not have to worry about downloading the correct dependencies. Resolver will download all needed dependencies, compile them and setup the IDE projects. All automatically.

### Getting Started

1. Create a resolver.json file for your project. Take a look at the [configuration reference](https://github.com/pixelpolishers/resolver/wiki/Configuration-Reference) or take a look at some examples:
  * [pixelpolishers/illusions.entity](https://raw.githubusercontent.com/pixelpolishers/illusions.entity/master/resolver.json)
  * [pixelpolishers/pp.system](https://raw.githubusercontent.com/pixelpolishers/pp.system/master/resolver.json)

2. To install dependencies run `resolver update`

3. Resolver can compile all dependencies for you, just run `resolver compile -d` to compile the dependencies.

4. To generate project files run `resolver generate [generator name]` (e.g. `resolver generate vs2010`)

### Documentation

Take a look at the [project wiki](https://github.com/pixelpolishers/resolver/wiki)
