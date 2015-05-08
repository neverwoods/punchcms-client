#!/usr/bin/env node
'use strict';
var pkg = require('./package.json');
var fs = require('fs');
var changelog = require('conventional-changelog');

changelog({
  version: pkg.version
}, function(err, log) {
  if (err) {
    throw new Error(err);
  }

  fs.writeFileSync('CHANGELOG.md', log);
});
