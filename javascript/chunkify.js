/**
 * Chunkify
 * Google Chrome Speech Synthesis Chunking Pattern
 * Fixes inconsistencies with speaking long texts in speechUtterance objects 
 * Licensed under the MIT License
 *
 * Peter Woolley and Brett Zamir
 */
 
window.speechSynthesis.getVoices();

var createUtterance = function(text) {
    var $voices = window.speechSynthesis.getVoices();
    utterance = new SpeechSynthesisUtterance();
   
    for(i = 0; i < $voices.length ; i++) {
        
        if ($voices[i].name == 'Nicolas') {
            utterance.voice = $voices[i];
            break;
        }

        if ($voices[i].name == 'Thomas') {
            utterance.voice = $voices[i];
            break;
        }
    }
    
    console.log( $voices );
    console.log( utterance.voice );
   
    utterance.rate = 1;
    utterance.text = text;
    utterance.volume = 0.4;
    return utterance;
}

 
var speechUtteranceChunker = function (utt, settings, callback) {
    settings = settings || {};
    var newUtt;
    var txt = (settings && settings.offset !== undefined ? utt.text.substring(settings.offset) : utt.text);
    if (utt.voice && utt.voice.voiceURI === 'native') { // Not part of the spec
        newUtt = utt;
        newUtt.text = txt;
        newUtt.addEventListener('end', function () {
            if (speechUtteranceChunker.cancel) {
                speechUtteranceChunker.cancel = false;
            }
            if (callback !== undefined) {
                callback();
            }
        });
    }
    else {
        var chunkLength = (settings && settings.chunkLength) || 160;
        var pattRegex = new RegExp('^[\\s\\S]{' + Math.floor(chunkLength / 2) + ',' + chunkLength + '}[.!?,]{1}|^[\\s\\S]{1,' + chunkLength + '}$|^[\\s\\S]{1,' + chunkLength + '} ');
        var chunkArr = txt.match(pattRegex);
 
        if (chunkArr[0] === undefined || chunkArr[0].length <= 2) {
            //call once all text has been spoken...
            if (callback !== undefined) {
                callback();
            }
            return;
        }
        var chunk = chunkArr[0];
        newUtt = new SpeechSynthesisUtterance(chunk);
        newUtt.voice = utt.voice;
        newUtt.rate = utt.rate;
        newUtt.volume = utt.volume;

        var x;
        for (x in utt) {
            if (utt.hasOwnProperty(x) && x !== 'text') {
                newUtt[x] = utt[x];
            }
        }
        newUtt.addEventListener('end', function () {
            if (speechUtteranceChunker.cancel) {
                speechUtteranceChunker.cancel = false;
                return;
            }
            settings.offset = settings.offset || 0;
            settings.offset += chunk.length - 1;
            speechUtteranceChunker(utt, settings, callback);
        });
    }
 
    if (settings.modifier) {
        settings.modifier(newUtt);
    }
    console.log(newUtt); //IMPORTANT!! Do not remove: Logging the object out fixes some onend firing issues.
    //placing the speak invocation inside a callback fixes ordering and onend issues.
    setTimeout(function () {
        speechSynthesis.speak(newUtt);
    }, 0);
};

/*
var read = function (data, callback) {
    speechUtteranceChunker(createUtterance(data), {chunkLength: 120}, callback);
}

*/

var read = function (data, callback) {
    
    if (voice_option != 'bruce') {
        speechUtteranceChunker(createUtterance(data), {chunkLength: 120}, callback);
        
    } else {
        var url = 'https://www.bing.com/tspeak?&format=audio/mp3&language=fr-fr&options=male&text=';
        url += encodeURI(data);

        var audio = new Audio(url);
        audio.onended = function() {
            sp.fadeOut();
        }
        audio.play();        
    }

}