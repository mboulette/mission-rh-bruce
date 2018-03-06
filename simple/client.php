<html>
<head>
    <style>
      .band {
          background-color: #3897e0;
          border-radius:3px 3px 0 0;
      }
    </style>

</head>
<body>

    <div id="visualiser">
    </div>

    <form>
        <textarea id="message"></textarea>
        <button id="btn_submit" type="button">submit</button>

        <button id="btn_bell" type="button">bell</button>
    </form>

    <div id="output">
    </div>

    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>
    <script type="text/javascript" src="javascript/spectrum.js"></script>
    <script type="text/javascript" src="javascript/chunkify.js"></script>
    <script type="text/javascript" src="javascript/simplepeer.min.js"></script>
    <script>

    var sp = makeSpectrum('visualiser', 250, 100, 12, 0.01);
    var server_id = 0;
    var p;

    var peer = function (stream) {

        p = new SimplePeer({
            initiator: false, 
            trickle: false,
            stream: stream,
            answerConstraints: { 
                mandatory: { 
                    OfferToReceiveAudio: false, 
                    OfferToReceiveVideo: false 
                } 
            }
        });

        p.on('error', function (err) {
            console.log('error', err)
        });

        p.on('signal', function (data) {
            console.log('SIGNAL', data);

            $.post( "rooter.php", {'id' : server_id, 'client' : JSON.stringify(data)}, function( data ) {
                console.log( data );
            });

        });

        p.on('connect', function () {
            console.log('CONNECT');
            p.send('Le client est connecté!');
        });

        p.on('data', function (data) {
            console.log(JSON.parse(data));
            data = JSON.parse(data);

            if (data.action == 'speech') {
                
                sp.fadeIn();
                read(data.msg, function(){
                    
                    console.log('test');
                    sp.fadeOut();
                });
            } else {
                $('#output').append('<div>'+data.msg+'</div>');
            }

        });


        $.get( "rooter.php", {'server' : true}, function( data ) {
            data = JSON.parse(data);

            console.log( data );
            server_id = data.id
            p.signal(data.server);
        });
       
    }


    $(function() {
    
        navigator.mediaDevices = navigator.mediaDevices || ((navigator.mozGetUserMedia || navigator.webkitGetUserMedia) ? {
            getUserMedia: function(c) {
                return new Promise(function(y, n) {
                    (navigator.mozGetUserMedia || navigator.webkitGetUserMedia).call(navigator, c, y, n);
             });
           }
        } : null);

        navigator.mediaDevices.getUserMedia({ audio: true, video: { width: 1280, height: 720 } })
        .then(function(stream) {     

            peer(stream);
        })
        .catch(function(err) {
            console.log(err.name + ": " + err.message);
        });

       $(document).on('click', '#btn_submit', function(event){
            event.preventDefault();
            event.stopPropagation();

            p.send( JSON.stringify({'action':'text', 'msg': $('#message').val()}) );
        });

       $(document).on('click', '#btn_bell', function(event){
            event.preventDefault();
            event.stopPropagation();

            p.send( JSON.stringify({'action':'bell', 'msg': 'Ring...'}) );
        });

    });

    </script>
</body>
</html>