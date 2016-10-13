var expect = require('chai').expect;
var assert = require('chai').assert;
var supertest = require('supertest');

var environments = {
  'prod':  'https://www.addthis.com/darkseid',
  'uat':   'https://www-uat.addthis.com/darkseid',
  'test':  'https://www-test.addthis.com/darkseid',
  'dev':   'https://www-dev.addthis.com/darkseid',
  'local': 'http://www-local.addthis.com/darkseid'
};

var darkseidUrl;
if (process.env.build_env && environments[process.env.build_env]) {
  darkseidUrl = environments[process.env.build_env];
} else {
  darkseidUrl = environments.test;
}

var request = supertest(darkseidUrl);

var json = 'application/json';
var pco = 'wpf';
var version = '2.0.0';

var getNewProfile = function(type, callback) {
  var username = 'julkaaddthis+integrationtests@gmail.com';
  var password = '1234'
  var goodBasicAuth = 'Basic ' + new Buffer(username + ':' + password).toString('base64');
  var date = new Date();
  var dateString = date.getTime();

  var body = {
    'type': type,
    'name': 'integration test ' + dateString
  };

  request
  .post('/publisher')
  .type('json')
  .set('Authorization', goodBasicAuth)
  .set('Content-Type', json)
  .set('Accept', json)
  .send(body)
  .end(function(err, res) {
    pubId = res.body.pubId;

    var cuidish = pubId.replace(/^ra-/, '');
    body = { 'name' : 'Integration Test (created '+ dateString +')' };

    request
    .post('/publisher/' + cuidish + '/application')
    .type('json')
    .set('Authorization', goodBasicAuth)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(body)
    .end(function(err, res) {
      var newApiKeyInfo = res.body.pop();
      apiKey = newApiKeyInfo.cuid;

      callback(pubId, apiKey);
    });
  });
};

var confirmToolSettingsMatch = function(expectedSettings, returnedSettings) {
  Object.keys(expectedSettings).forEach(function(field) {
    assert.property(returnedSettings, field);
    if (Array.isArray(expectedSettings[field]) && field ==='services') {
      expect(returnedSettings[field]).to.deep.include.members(expectedSettings[field]);
    } else if (Array.isArray(expectedSettings[field])) {
      assert.equal(expectedSettings[field].length, returnedSettings[field].length);
      expect(returnedSettings[field]).to.include.members(expectedSettings[field]);
    } else {
      assert.equal(expectedSettings[field], returnedSettings[field]);
    }
  });
};

var checkBoostSettingsFormat = function(boostSettings, desiredToolPco) {
  var desiredToolSettings;

  expect(boostSettings).to.be.a('object');
  expect(boostSettings.subscription).to.be.a('object');
  expect(boostSettings.subscription.edition).to.be.a('string');
  expect(boostSettings.templates).to.be.a('array');
  Object.keys(boostSettings.templates).forEach(function(key) {
    expect(boostSettings.templates[key]).to.be.a('object');
    if (boostSettings.templates[key].id === '_default') {
      expect(boostSettings.templates[key].widgets).to.be.a('array');
      boostSettings.templates[key].widgets.forEach(function(toolSettings) {
        expect(toolSettings).to.be.a('object');
        if ((typeof desiredToolPco === 'string') &&
          desiredToolPco === toolSettings.id
        ) {
          desiredToolSettings = toolSettings;
        }
      });
    }
  });

  return desiredToolSettings;
};

describe('WordPress compatibility check endpoint', function() {
  this.timeout(5000);
  var unsupportedPluginVersions = {
    // Website Tools by AddThis
    'wpwt': [],
    // Follow Buttons by AddThis
    'wpf': [],
    // Related Posts by AddThis
    'wprp': [],
    // Smart Layers by AddThis
    'wpsl': [],
    // Share Buttons by AddThis
    'wpp': []
  };

  var supportedPluginVersions = {
    // Website Tools by AddThis
    'wpwt': ['1.0.0', '1.0.1', '1.0.2', '1.1.0', '1.1.1', '1.1.2'],
    // Follow Buttons by AddThis
    'wpf': ['2.0.0', '2.0.1', '2.0.2', '3.0.0'],
    // Related Posts by AddThis
    'wprp': ['1.0.0'],
    // Smart Layers by AddThis
    'wpsl': ['2.0.0'],
    // Share Buttons by AddThis
    'wpp': []
  };

  // make sure it is returning a good results for all supported versions
  Object.keys(supportedPluginVersions).forEach(function(pluginPco) {
    supportedPluginVersions[pluginPco].forEach(function(version) {
      it(pluginPco + '-' + version + ' is supported', function(done) {
        request
        .get('/plugins/'+pluginPco+'/v/'+version+'/check')
        .expect(204, done);
      });
    });
  });
  // should also check for expected result for unsupported versions, but there are none yet
});

describe('WordPress registration process on existing account', function() {
  this.timeout(5000);
  var pubIdsOnProfile;
  var wptypePubId;
  var defaultPubId;
  var wpApiKey;
  var defaultApiKey;
  var username = 'julkaaddthis+integrationtests@gmail.com';
  var password = '1234'
  var goodBasicAuth = 'Basic ' + new Buffer(username + ':' + password).toString('base64');
  var badBasicAuth = 'Basic ' + new Buffer(username + ':' + password + '9876').toString('base64');

  it('validates good login', function(done) {
    request
    .get('/user')
    .set('Authorization', goodBasicAuth)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body.email).to.equal(username);
      done(err, res);
    });
  });

  it('rejects bad login', function(done) {
    request
    .get('/user')
    .set('Authorization', badBasicAuth)
    .set('Accept', json)
    .expect(401, done);
  });

  it('retrieves pubids for account', function(done) {
    request
    .get('/publisher')
    .set('Authorization', goodBasicAuth)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      pubIdsOnProfile = res.body;
      expect(pubIdsOnProfile).to.be.a('array');
      expect(pubIdsOnProfile.length).to.be.above(0);
      done(err, res);
    });
  });

  it('rejects request for pubids for account with bad basic auth', function(done) {
    request
    .get('/publisher')
    .set('Authorization', badBasicAuth)
    .set('Accept', json)
    .expect(401, done);
  });

  it('found pubid named wptype', function(done) {
    pubIdsOnProfile.forEach(function(pubIdInfo) {
      if (pubIdInfo.name === 'wptype') {
        wptypePubId = pubIdInfo.pubId;
      }
    }, this);
    expect(wptypePubId).to.be.a('string');
    done();
  });

  it('validates wp pubid is real and type is wp', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+version+'/site/'+wptypePubId)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body.type).to.equal('wp');
      done(err, res);
    });
  });

  it('creates a new api key for pubid with wp type', function(done) {
    var cuidish = wptypePubId.replace(/^ra-/, '');
    var date = new Date();
    var dateString = date.getTime();
    var body = { 'name' : 'Integration Test (created '+ dateString +')' };

    request
    .post('/publisher/' + cuidish + '/application')
    .type('json')
    .set('Authorization', goodBasicAuth)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(body)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('array');
      var newApiKeyInfo = res.body.pop();
      expect(newApiKeyInfo).to.be.a('object');
      expect(newApiKeyInfo.cuid).to.be.a('string');
      wpApiKey = newApiKeyInfo.cuid;
      done(err, res);
    });
  });

  it('found pubid named My Site', function(done) {
    pubIdsOnProfile.forEach(function(pubIdInfo) {
      if (pubIdInfo.name === 'My Site') {
        defaultPubId = pubIdInfo.pubId;
      }
    }, this);
    expect(defaultPubId).to.be.a('string');
    done();
  });

  it('validates default pubid is real and type is none', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+version+'/site/'+defaultPubId)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body.type).to.equal('none');
      done(err, res);
    });
  });

  it('creates an new api key for pubid with none type', function(done) {
    var cuidish = defaultPubId.replace(/^ra-/, '');
    var date = new Date();
    var dateString = date.getTime();
    var body = { 'name' : 'Integration Test (created '+ dateString +')' };

    request
    .post('/publisher/' + cuidish + '/application')
    .type('json')
    .set('Authorization', goodBasicAuth)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(body)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('array');
      var newApiKeyInfo = res.body.pop();
      expect(newApiKeyInfo).to.be.a('object');
      expect(newApiKeyInfo.cuid).to.be.a('string');
      defaultApiKey = newApiKeyInfo.cuid;
      done(err, res);
    });
  });

  it('changes default pubid to type wp', function(done) {
    var type = 'wp';
    var body = { 'type' : type };

    request
    .put('/publisher/' + defaultPubId + '/profile-type')
    .type('json')
    .set('Authorization', defaultApiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(body)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body.type).to.equal(type);
      done(err, res);
    });
  });

  it('validates default pubid is real and type is wp now', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+version+'/site/'+defaultPubId)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body.type).to.equal('wp');
      done(err, res);
    });
  });

  it('changes default pubid to type none', function(done) {
    var type = 'none';
    var body = { 'type' : type };

    request
    .put('/publisher/' + defaultPubId + '/profile-type')
    .type('json')
    .set('Authorization', defaultApiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(body)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body.type).to.equal(type);
      done(err, res);
    });
  });

  it('validates default pubid is real and type is none again', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+version+'/site/'+defaultPubId)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body.type).to.equal('none');
      done(err, res);
    });
  });

  it('validates a good api key in Authorization header', function(done) {
    var cuidish = wptypePubId.replace(/^ra-/, '');

    request
    .get('/publisher/' + cuidish + '/application')
    .set('Authorization', wpApiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('array');
      var match = false;
      res.body.forEach(function(apiKeyInfo) {
        expect(apiKeyInfo).to.be.a('object');
        expect(apiKeyInfo.cuid).to.be.a('string');
        if (apiKeyInfo.cuid === wpApiKey) { match = true; }
      });
      expect(match).to.equal(true);
      done(err, res);
    });
  });

  it('rejects a bad api key in Authorization header', function(done) {
    var cuidish = wptypePubId.replace(/^ra-/, '');

    request
    .get('/publisher/' + cuidish + '/application')
    .set('Authorization', 'gibberish_for_integration_test')
    .set('Accept', json)
    .expect(404, done);
  });

  // not yet used in production
  it('validates a good api key in X_Api_Key header', function(done) {
    var cuidish = wptypePubId.replace(/^ra-/, '');

    request
    .get('/publisher/' + cuidish + '/application')
    .set('X_Api_Key', wpApiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('array');
      var match = false;
      res.body.forEach(function(apiKeyInfo) {
        expect(apiKeyInfo).to.be.a('object');
        expect(apiKeyInfo.cuid).to.be.a('string');
        if (apiKeyInfo.cuid === wpApiKey) { match = true; }
      });
      expect(match).to.equal(true);
      done(err, res);
    });
  });

  // not yet used in production
  it('rejects a bad api key in X_Api_Key header', function(done) {
    var cuidish = wptypePubId.replace(/^ra-/, '');

    request
    .get('/publisher/' + cuidish + '/application')
    .set('X_Api_Key', 'gibberish_for_integration_test')
    .set('Accept', json)
    .expect(404, done);
  });
});

describe('WordPress registration process on new account ', function() {
  this.timeout(5000);
  var date = new Date();
  var dateString = date.getTime();
  var username = 'julkaaddthis+integrationtests'+dateString+'@gmail.com';
  var password = '1234'
  var goodBasicAuth = 'Basic ' + new Buffer(username + ':' + password).toString('base64');
  var badBasicAuth = 'Basic ' + new Buffer(username + ':' + password + '9876').toString('base64');

  var pluginPco = 'wpwt';
  var pubIdsOnProfile;
  var pubId;

  it('creates a new account', function(done) {
    this.retries(4);

    var body = {
      'username': username,
      'email': username,
      'plainPassword': password,
      'subscribedToNewsletter': true,
      'profileType': 'wp',
      'source': pluginPco,
    };

    request
    .post('/account/register-user')
    .type('json')
    .set('Content-Type', json)
    .set('Accept', json)
    .send(body)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body.id).to.be.a('string');
      done(err, res);
    });
  });

  it('validates good login', function(done) {
    request
    .get('/user')
    .set('Authorization', goodBasicAuth)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body.email).to.equal(username);
      done(err, res);
    });
  });

  it('rejects bad login', function(done) {
    request
    .get('/user')
    .set('Authorization', badBasicAuth)
    .set('Accept', json)
    .expect(401, done);
  });

  it('retrieves pubids for account', function(done) {
    request
    .get('/publisher')
    .set('Authorization', goodBasicAuth)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      pubIdsOnProfile = res.body;
      expect(pubIdsOnProfile).to.be.a('array');
      expect(pubIdsOnProfile.length).to.be.above(0);
      done(err, res);
    });
  });

  it('found pubid named My Site', function(done) {
    pubIdsOnProfile.forEach(function(pubIdInfo) {
      if (pubIdInfo.name === 'My Site') {
        pubId = pubIdInfo.pubId;
      }
    }, this);
    expect(pubId).to.be.a('string');
    done();
  });

  it.skip('validates default pubid is real and type is wp (T64290)', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+version+'/site/'+pubId)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body.type).to.equal('wp');
      done(err, res);
    });
  });

  it('creates an new api key for default pubid', function(done) {
    var cuidish = pubId.replace(/^ra-/, '');
    var date = new Date();
    var dateString = date.getTime();
    var body = { 'name' : 'Integration Test (created '+ dateString +')' };

    request
    .post('/publisher/' + cuidish + '/application')
    .type('json')
    .set('Authorization', goodBasicAuth)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(body)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('array');
      var newApiKeyInfo = res.body.pop();
      expect(newApiKeyInfo).to.be.a('object');
      expect(newApiKeyInfo.cuid).to.be.a('string');
      apiKey = newApiKeyInfo.cuid;
      done(err, res);
    });
  });

  it('validates a good api key in Authorization header', function(done) {
    var cuidish = pubId.replace(/^ra-/, '');

    request
    .get('/publisher/' + cuidish + '/application')
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('array');
      var match = false;
      res.body.forEach(function(apiKeyInfo) {
        expect(apiKeyInfo).to.be.a('object');
        expect(apiKeyInfo.cuid).to.be.a('string');
        if (apiKeyInfo.cuid === apiKey) { match = true; }
      });
      expect(match).to.equal(true);
      done(err, res);
    });
  });

  it('rejects a bad api key in Authorization header', function(done) {
    var cuidish = pubId.replace(/^ra-/, '');

    request
    .get('/publisher/' + cuidish + '/application')
    .set('Authorization', 'gibberish_for_integration_test')
    .set('Accept', json)
    .expect(404, done);
  });

  // not yet used in production
  it('validates a good api key in X_Api_Key header', function(done) {
    var cuidish = pubId.replace(/^ra-/, '');

    request
    .get('/publisher/' + cuidish + '/application')
    .set('X_Api_Key', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('array');
      var match = false;
      res.body.forEach(function(apiKeyInfo) {
        expect(apiKeyInfo).to.be.a('object');
        expect(apiKeyInfo.cuid).to.be.a('string');
        if (apiKeyInfo.cuid === apiKey) { match = true; }
      });
      expect(match).to.equal(true);
      done(err, res);
    });
  });

  // not yet used in production
  it('rejects a bad api key in X_Api_Key header', function(done) {
    var cuidish = pubId.replace(/^ra-/, '');

    request
    .get('/publisher/' + cuidish + '/application')
    .set('X_Api_Key', 'gibberish_for_integration_test')
    .set('Accept', json)
    .expect(404, done);
  });

  var createdPubId;
  it('creates a new profile of type wp', function(done) {
    var date = new Date();
    var dateString = date.getTime();

    var body = {
      'type': 'wp',
      'name': 'test ' + dateString
    };

    request
    .post('/publisher')
    .type('json')
    .set('Authorization', goodBasicAuth)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(body)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body.pubId).to.be.a('string');
      createdPubId = res.body.pubId;
      done(err, res);
    });
  });

  it('validates new pubid is type wp', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+version+'/site/'+createdPubId)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body.type).to.equal('wp');
      done(err, res);
    });
  });
});

describe('WordPress related post promoted URL functionality ', function() {
  this.timeout(5000);
  var toolPco = 'smlre';
  var testUrl1 = 'https://addthis.com';
  var testUrl2 = 'http://example.com';
  var pubId;
  var apiKey;

  it('recieves empty object of promote URL campaigns for new pubid', function(done) {
    getNewProfile('wp', function(aPubId, anApiKey) {
      pubId = aPubId;
      apiKey = anApiKey;
      request
      .get('/wordpress/site/'+pubId+'/campaigns')
      .set('Authorization', apiKey)
      .set('Content-Type', json)
      .set('Accept', json)
      .expect(200)
      .end(function(err, res) {
        expect(res.body).to.be.a('object');
        expect(Object.keys(res.body)).to.be.empty;
        done(err, res);
      });
    });
  });

  it('promotes first URL for a related posts tool ' + toolPco, function(done) {
    request
    .post('/wordpress/site/'+pubId+'/campaigns/'+toolPco)
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send([testUrl1])
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body[toolPco]).to.be.a('array');
      assert.equal(res.body[toolPco].length, 1);
      var url = res.body[toolPco].pop();
      assert.equal(url, testUrl1);
      done(err, res);
    });
  });

  it('recieves object with the first promoted URL campaign for tool ' + toolPco, function(done) {
    request
    .get('/wordpress/site/'+pubId+'/campaigns')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body[toolPco]).to.be.a('array');
      assert.equal(res.body[toolPco].length, 1);
      var url = res.body[toolPco].pop();
      assert.equal(url, testUrl1);
      done(err, res);
    });
  });

  it('change promoted URL campaign to use second url for tool ' + toolPco, function(done) {
    request
    .post('/wordpress/site/'+pubId+'/campaigns/'+toolPco)
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send([testUrl2])
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body[toolPco]).to.be.a('array');
      assert.equal(res.body[toolPco].length, 1);
      var url = res.body[toolPco].pop();
      assert.equal(url, testUrl2);
      done(err, res);
    });
  });

  it('recieves object with only the second URL in the promoted URL campaign', function(done) {
    request
    .get('/wordpress/site/'+pubId+'/campaigns')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body[toolPco]).to.be.a('array');
      assert.equal(res.body[toolPco].length, 1);
      var url = res.body[toolPco].pop();
      assert.equal(url, testUrl2);
      done(err, res);
    });
  });

  it('deletes a promoted URL campaign', function(done) {
    request
    .delete('/wordpress/site/'+pubId+'/campaigns/'+toolPco)
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(Object.keys(res.body)).to.be.empty;
      done(err, res);
    });
  });

  it('confirms there are no promote URL campaigns left on new pubid', function(done) {
    request
    .get('/wordpress/site/'+pubId+'/campaigns')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(Object.keys(res.body)).to.be.empty;
      done(err, res);
    });
  });
});

describe('WordPress related boost get/update functionality ', function() {
  this.timeout(5000);
  var pubId;
  var apiKey;
  var floatingToolPco = 'smlre'; // Recommended Content Footer
  var inlineToolPco = 'flwh'; // Horizontal Follow Buttons
  var initialFloatingToolSettings = {
    'id': floatingToolPco,
    'enabled': true,
    'maxitems': 3,
    'numrows': 1,
    'responsive': '300px',
    'theme': 'dark',
    'title': 'Read This',
    '__hideOnHomepage': true,
    '__hideOnUrls': [
      'http://www.example.com/test1',
      'http://www.example.com/test2'
    ],
  };
  var updatedFloatingToolSettings = {
    'id': floatingToolPco,
    'enabled': true,
    'maxitems': 2,
    'numrows': 3,
    'responsive': '700px',
    'theme': 'light',
    'title': 'Reccommended for you',
    '__hideOnHomepage': false,
    '__hideOnUrls': [
      'http://www.example.com/test3',
      'http://www.example.com/test4'
    ],
  };
  var initialInlineToolSettings = {
    'id': inlineToolPco,
    'enabled': true,
    'title': 'I am social',
    'size': 'large',
    'services': [
      {
        'service': 'facebook',
        'usertype': 'id',
        'id': 1234
      },
      {
        'service': 'linkedin',
        'usertype': 'company',
        'id': 'addthis'
      }
    ]
  };
  var updatedInlineToolSettings = {
    'id': inlineToolPco,
    'enabled': true,
    'title': 'I am social',
    'size': 'small',
    'services': [
      {
        'service': 'linkedin',
        'usertype': 'company',
        'id': 'addthis'
      },
      {
        'service': 'twitter',
        'usertype': 'user',
        'id': 'addthis'
      }
    ]
  };

  it('recieves empty boost settings for new pubid', function(done) {
    getNewProfile('wp', function(aPubId, anApiKey) {
      pubId = aPubId;
      apiKey = anApiKey;
      request
      .get('/plugins/'+pco+'/v/'+version+'/site/'+pubId)
      .set('Authorization', apiKey)
      .set('Accept', json)
      .expect(200)
      .end(function(err, res) {
        checkBoostSettingsFormat(res.body);
        expect(res.body.templates).to.be.empty;
        done(err, res);
      });
    });
  });

  it('creates floating tool boost settings', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+version+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(initialFloatingToolSettings)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = checkBoostSettingsFormat(res.body, initialFloatingToolSettings.id);
      confirmToolSettingsMatch(initialFloatingToolSettings, toolSettings);
      done(err, res);
    });
  });

  it('confirms floating tool boost settings', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+version+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = checkBoostSettingsFormat(res.body, initialFloatingToolSettings.id);
      confirmToolSettingsMatch(initialFloatingToolSettings, toolSettings);
      done(err, res);
    });
  });

  it('changes floating tool boost settings', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+version+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(updatedFloatingToolSettings)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = checkBoostSettingsFormat(res.body, updatedFloatingToolSettings.id);
      confirmToolSettingsMatch(updatedFloatingToolSettings, toolSettings);
      done(err, res);
    });
  });

  it('confirms floating tool has desired new boost settings', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+version+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = checkBoostSettingsFormat(res.body, updatedFloatingToolSettings.id);
      confirmToolSettingsMatch(updatedFloatingToolSettings, toolSettings);
      done(err, res);
    });
  });

  it('disables floating tool boost settings', function(done) {
    updatedFloatingToolSettings.enabled  = false;

    request
    .put('/plugins/'+pco+'/v/'+version+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(updatedFloatingToolSettings)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = checkBoostSettingsFormat(res.body, updatedFloatingToolSettings.id);
      confirmToolSettingsMatch(updatedFloatingToolSettings, toolSettings);
      done(err, res);
    });
  });

  it('confirms floating tool is disabled', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+version+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = checkBoostSettingsFormat(res.body, updatedFloatingToolSettings.id);
      confirmToolSettingsMatch(updatedFloatingToolSettings, toolSettings);
      done(err, res);
    });
  });

  it('creates inline tool boost settings', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+version+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(initialInlineToolSettings)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = checkBoostSettingsFormat(res.body, initialInlineToolSettings.id);
      confirmToolSettingsMatch(initialInlineToolSettings, toolSettings);
      done(err, res);
    });
  });

  it('confirms inline tool boost settings', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+version+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = checkBoostSettingsFormat(res.body, initialInlineToolSettings.id);
      confirmToolSettingsMatch(initialInlineToolSettings, toolSettings);
      done(err, res);
    });
  });

  it('changes inline tool boost settings', function(done) {
    request
    .put('/plugins/'+pco+'/v/'+version+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(updatedInlineToolSettings)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = checkBoostSettingsFormat(res.body, updatedInlineToolSettings.id);
      confirmToolSettingsMatch(updatedInlineToolSettings, toolSettings);
      done(err, res);
    });
  });

  it('confirms inline tool has desired new boost settings', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+version+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = checkBoostSettingsFormat(res.body, updatedInlineToolSettings.id);
      confirmToolSettingsMatch(updatedInlineToolSettings, toolSettings);
      done(err, res);
    });
  });

  it('disable inline tool', function(done) {
    updatedInlineToolSettings.enabled = false;

    request
    .put('/plugins/'+pco+'/v/'+version+'/site/'+pubId+'/widget')
    .set('Authorization', apiKey)
    .set('Content-Type', json)
    .set('Accept', json)
    .send(updatedInlineToolSettings)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = checkBoostSettingsFormat(res.body, updatedInlineToolSettings.id);
      confirmToolSettingsMatch(updatedInlineToolSettings, toolSettings);
      done(err, res);
    });
  });

  it('confirms inline tool is disabled', function(done) {
    request
    .get('/plugins/'+pco+'/v/'+version+'/site/'+pubId)
    .set('Authorization', apiKey)
    .set('Accept', json)
    .expect(200)
    .end(function(err, res) {
      var toolSettings = checkBoostSettingsFormat(res.body, updatedInlineToolSettings.id);
      confirmToolSettingsMatch(updatedInlineToolSettings, toolSettings);
      done(err, res);
    });
  });
});

describe('Look up subscription type for PRO and not Pro pubids ', function() {
  this.timeout(5000);
  var basicPubId;
  var proPubId = 'atblog';

  it('confirms pubid ' + proPubId + ' has a PRO subscription', function(done) {
    request
    .get('/wordpress/site/' + proPubId)
    .expect(200)
    .end(function(err, res) {
      expect(res.body).to.be.a('object');
      expect(res.body.subscription).to.be.a('object');
      expect(res.body.subscription.edition).to.be.a('string');
      expect(res.body.subscription.edition).to.equal('PRO');
      done(err, res);
    });
  });

  it('confirms a new pubis does not have a PRO subscription', function(done) {
    getNewProfile('wp', function(aPubId, anApiKey) {
      basicPubId = aPubId;
      request
      .get('/wordpress/site/'+basicPubId)
      .expect(200)
      .end(function(err, res) {
        expect(res.body).to.be.a('object');
        expect(res.body.subscription).to.be.a('object');
        expect(res.body.subscription.edition).to.be.a('string');
        expect(res.body.subscription.edition).to.not.equal('PRO');
        done(err, res);
      });
    });
  });
});