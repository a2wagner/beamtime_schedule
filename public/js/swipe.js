function init() {
    "use strict";
    var code = [38, 38, 40, 40, 37, 39, 37, 39],
        correctActionCount = 0,
        nextAction = code[correctActionCount],
        usingTouch = false,
        buttonsLoaded = false,
        btns = null,
        btnCount = 0,

        // will contain starting and ending X and Y coords of touch gesture
        actionCoords = {
            startX: null,
            startY: null,

            endX: null,
            endY: null
        },

        reset = function () {
            correctActionCount = 0;
            btnCount = 0;
            nextAction = code[correctActionCount];
            // hide canvas if buttons loaded
            if (usingTouch && buttonsLoaded)
                document.getElementById('nes').style.display = 'none';
        },

        setHeight = function () {
            window.outerHeight = (window.screen.height / 3) * 2;
        },

        simulateKeyPress = function (keycode) {
            //console.log('trigger event: ' + keycode);
            var e = jQuery.Event("keydown");
            e.which = keycode;  // key code value
            e.keyCode = keycode;
            jQuery(document).trigger(e);
            //jQuery.event.trigger({ type : 'keydown', which : character/*.charCodeAt(0)*/ });
        },

        respondTo = function (input) {
            if ((input == 65 || input == 66) && correctActionCount === code.length)
                btnCount++;
            else if (correctActionCount === code.length)
                reset();
            // current action was the next correct step in the sequence
            if (input === nextAction || (correctActionCount === code.length && btnCount < 2)) {
                //console.log('input: ' + input);
                // sequence not yet complete
                if (correctActionCount < code.length) {
                    correctActionCount += 1;
                    nextAction = code[correctActionCount];
                }
                // if using touch, display controller when ready for B A input
                if (usingTouch && correctActionCount === code.length && btnCount < 2 && buttonsLoaded) {
                    document.getElementById('nes').style.display = 'block';
                }
            // current action not next correct step in sequence
            } else {
                // reset sequence
                reset();
                // handle if incorrect key restarts sequence
                if (input === code[0]) {
                    correctActionCount = 1;
                    nextAction = code[correctActionCount];
                }
            }
        },

        // listen for code key sequence
        setupKeyEvents = function () {
            document.addEventListener('keyup', function (e) {
                respondTo(e.keyCode);
            }, false);
        },

        // listen for button code key sequence
        setupButtonEvents = function () {
            document.addEventListener('keydown', function (e) {
                if (e.keyCode == 65 || e.keyCode == 66)
                    btnCount++;
                if (btnCount == 2)
                    reset();
                //console.log("key: ", e.keyCode);
            }, false);
        },

        // listen for code touch sequence
        setupTouchEvents = function () {
            var determineSwipeDirection = function (action) {
                    var itm,
                        deltaX,
                        deltaY,
                        absDeltaX,
                        absDeltaY,
                        tmpXY,
                        threshold = 100;  // tweak this value to fine tune swipe detection
                    // coord differences between start and end point of touch
                    deltaX = action.endX - action.startX;
                    deltaY = action.endY - action.startY;

                    absDeltaX = Math.abs(deltaX);
                    absDeltaY = Math.abs(deltaY);

                    // if differences > threshold in either plane, it's significant
                    if (Math.abs(deltaX) > threshold || Math.abs(deltaY) > threshold) {
                        if (absDeltaY > 1.5 * absDeltaX) {
                            if (deltaY > threshold) {
                                respondTo(40);
                                simulateKeyPress(40);
                                console.log('swipe down');
                            } else if (deltaY < -threshold) {
                                respondTo(38);
                                simulateKeyPress(38);
                                console.log('swipe up');
                            }
                        } else if (absDeltaX > 1.5 * absDeltaY) {
                            if (deltaX > threshold) {
                                respondTo(39);
                                simulateKeyPress(39);
                                console.log('swipe right');
                            } else if (deltaX < -threshold) {
                                respondTo(37);
                                simulateKeyPress(37);
                                console.log('swipe left');
                            }
                        }
                    }
                },
                terminateTouch = function (ev) {
                    // record ending X, Y of touch in the action object
                    actionCoords.endX = ev.changedTouches[0].screenX;
                    actionCoords.endY = ev.changedTouches[0].screenY;
                    determineSwipeDirection(actionCoords);

                };

            document.addEventListener('touchstart', function (e) {
                // record starting X, Y of touch in the action object
                //e.preventDefault();
                actionCoords.startX = e.touches[0].screenX;
                actionCoords.startY = e.touches[0].screenY;
            }, false);

            document.addEventListener('touchend', function (e) {
                //e.preventDefault();
                terminateTouch(e);
            }, false);

            document.addEventListener('touchcancel', function (e) {
                //e.preventDefault();
                terminateTouch(e);
            }, false);
        },

        drawButtons = function (img, xPos, yPos, width, height) {
            var canvas = document.createElement('canvas'),
                ctx = canvas.getContext('2d'),
                buttonsLoaded = true,

                // An onclick handler for a canvas tag. Assumes a path is currently defined.
                hittest = function (canvas, xIn, yIn) {
                    var c = canvas.getContext("2d"),
                        // Get the canvas size and position
                        bb = canvas.getBoundingClientRect(),
                        // Convert mouse event coordinates to canvas coordinates
                        x = (xIn-bb.left)*(canvas.width/bb.width),
                        y = (yIn-bb.top)*(canvas.height/bb.height);

                    // Register button click
                    if (c.isPointInPath(x,y)) {
                        if (x < 340) { // button B
                            respondTo(66);
                            simulateKeyPress(66);
                            btnCount++;
                        } else {
                            respondTo(65);
                            simulateKeyPress(65);
                            btnCount++;
                        }
                    }};
            canvas.id = 'nes',
            //document.getElementsByClassName('page-header')[0].appendChild(canvas);
            //document.getElementsByClassName('container')[1].insertBefore(canvas, document.getElementsByTagName('canvas')[0]);
            document.getElementsByClassName('container')[1].insertBefore(canvas, document.getElementsByClassName('col-lg-6')[0]);

            canvas.width = width;
            canvas.height = height
            // insert the image
            ctx.drawImage(img, xPos, yPos);

            // create path overlays on the buttons, then detect clicks on these paths
            ctx.beginPath();
            ctx.strokeStyle = '#ff2530';
            ctx.fillStyle = '#ff2530';
            ctx.lineWidth = 5;
            ctx.arc(313, 130, 19, 0, (Math.PI/180)*360, false);
            ctx.moveTo(375, 165);
            ctx.arc(370, 130, 19, 0, (Math.PI/180)*360, false);
            //ctx.stroke();
            ctx.fill();
            ctx.closePath();

            canvas.addEventListener('click', function (e) {
                hittest(this, e.clientX, e.clientY);  // 'this' is the canvas target of the event
            }, false);
            /* do not use touchstart as with no e.preventDefault in touch listeners above both get triggered, touchstart and click
             * firefox on android seems to interpret a touch on the screen as both a click and a touch event
             * when preventDefault() is not used (which seems to break scrolling...)
             * where chrome on android only listens to click events in this case and ignores touchstart completely... ARGH!
            canvas.addEventListener('touchstart', function (e) {
                hittest(this, e.touches[0].screenX, e.touches[0].screenY);  // 'this' is the canvas target of the event
            }, false);*/
        },

        setup = function () {
            btns = document.createElement('img');
            btns.className = 'controller';
            btns.src = '/img/controller.png';
            setHeight();
            //setupKeyEvents();  // don't use key events together with swipe gestures
            setupButtonEvents();

            if (window.TouchEvent !== undefined) {
                usingTouch = true;
                setupTouchEvents();
            }

            btns.onload = function () {
                // Passed arguments: btns, xPos, yPos, width, height
                //  where width and height are the intrinsic dimensions of
                //  the image loading into buttons.

                drawButtons(btns, 0, 0, btns.naturalWidth, btns.naturalHeight);
                buttonsLoaded = true;
            };

        };

    setup();
};
