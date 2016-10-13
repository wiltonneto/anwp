appAddThisWordPress.filter('toolType', function() {
  return function(input, type) {
    var output = {};
    var pcos = [];

    if (type === 'follow') {
      pcos = [
        'cflwh',
        'flwh',
        'flwv',
        'smlfw'
      ];
    } else if (type === 'recommended') {
      pcos = [
        'cod',
        'jrcf',
        'smlre',
        'smlrebh',
        'smlrebv',
        'smlwn',
        'tst',
        'wnm'
      ];
    } else { // share
      pcos = [
        'cmtb',
        'ctbx',
        'ist',
        'jsc',
        'msd',
        'newsletter',
        'resh',
        'scopl',
        'smlmo',
        'smlsh',
        'smlshp',
        'tbx'
      ];
    }

    angular.forEach(input, function(value, key) {
      if (pcos.indexOf(key) > -1) {
        output[key] = value;
      }
    });

    return output;
  };
});