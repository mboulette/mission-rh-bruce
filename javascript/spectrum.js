/**
 *    Turn an element into a virtual spectrum,
 *    Ken Fyrstenberg Nilsen, Public domain.
 *
 *    USAGE:
 *        makeSpectrum(id, width, height)
 *        makeSpectrum(id, width, height, bands)
 *        makeSpectrum(id, width, height, bands, volume)
 *
 *    id      id of the element to be converted into spectrum
 *    width   width in pixels of spectrum
 *    height  height in pixels of spectrum
 *    bands   (optional) number of "bands"
 *    volume  initial volume (0-1)
 *
 *    METHODS:
 *
 *    setVolume()    returns current volume
 *    setVolume(vol) sets new volume (0-1 float)
*/
function makeSpectrum(id, width, height, bands, volume) {

    bands = bands ? bands : 12;
    volume = volume ? volume : 0;
    
    if (bands < 1) bands = 1;
    if (bands > 128) bands = 128;
    
    // init parent element
    var parent = document.getElementById(id),
        bandElements = [];
    
    if (typeof parent === 'undefined')
        alert('Element ' +id + ' not found!');
    
    parent.style.display = 'block';
    parent.style.width = width + 'px';
    parent.style.height = height + 'px';
    parent.style.position = 'relative';

    var bandValues = [],
        oldBandValues = [],
        bw = (((width)/ bands) |0),
        me = this;

    function calcBand(bandNum) {
        var bv = bandValues[bandNum],
            obv = oldBandValues[bandNum];

        if (bv >= obv) obv = bv;
        obv -= 0.1;
        if (obv < 0 ) obv = 0;
        obv *= volume;        
        
        oldBandValues[bandNum] = obv;
        return obv;
    }
    
    function getFFT(band) {
        band = band ? band : bandValues;
        for(var i = 0; i < bands; i++) {
            band[i] = Math.random();
       }
       //"BPM" to affect first bar
       var d = (new Date()).getMilliseconds() % 10;
       band[0] = band[0] * 0.2 + (d / 10) * 0.8;
       if (bands > 1) band[1] = band[1] * 0.3 + (d / 10) * 0.7;
       if (bands > 2) band[2] = band[2] * 0.5 + (d / 10) * 0.5;
    }    

    function createBands() {
       
        var i, html = '';
        for(i = 0; i < bands; i++) {
            html += '<div id="' + id + '_band' + i + '" ';
            html += 'style="display:block;position:absolute;';
            html += 'left:' + ((i * bw + 1)|0);
            html += 'px;top:' + height;
            html += 'px;width:' + (bw - 2);
            html += 'px;height:0';
            html += 'px;" class="band"></div>';
        }
        parent.innerHTML = html;

        for(i = 0; i < bands; i++) {
            var el = document.getElementById(id + '_band' + i);
            bandElements.push(el);
        }
    }    
    this.setVolume = function(vol) {
        
        if (arguments.length === 0)
            return volume;
 
        if (vol < 0) vol = 0;
        if (vol > 1) vol = 1;
        volume = vol;
    }
    this.setVolume(volume);
    
    this.fadeIn = function() {
        volume += 0.2;
        sp.setVolume(volume);
        if (volume < 1) setTimeout(this.fadeIn, 60);
    }

    this.fadeOut = function() {
        volume -= 0.2;
        sp.setVolume(volume);
        if (volume > 0) setTimeout(this.fadeOut, 60);
    }

    this.createSnapshot = function() {
    
        var h, y, el;
        
        getFFT(bandValues);    
        
        for(var i = 0; i < bands; i++) {
            h = calcBand(i);
            el = bandElements[i].style;
            el.top = ((height - height * h)|0) + 'px';
            el.height =  ((height * h)|0) + 'px';
        }
    }

    //init bands
    getFFT(oldBandValues);
    createBands();

    //GO
    setInterval(me.createSnapshot, 100);
    
    return this;
}