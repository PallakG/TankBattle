
<!DOCTYPE html>

<html>
	<head>
	<link href="<?= base_url()?>css/template.css" rel="stylesheet">
	<script src="http://code.jquery.com/jquery-latest.js"></script>
	<script src="<?= base_url() ?>/js/jquery.timers.js"></script>
	<script src="http://d3lp1msu2r81bx.cloudfront.net/kjs/js/lib/kinetic-v4.5.5.min.js"></script>
	<script>

		function toRadians (angle) {
			  return angle * (Math.PI / 180);
		}
		
		function translateTank(layer, tank, arguments, turret){

			var url = "<?= base_url() ?>combat/postTankCoords";
			$.post(url,arguments, function (data,textStatus,jqXHR){
					//invitie
				meta = $.parseJSON(data);
				if (meta && meta.status=='success') {
					userCoords.x1 = meta.x1;
					userCoords.y1 = meta.y1;
					userCoords.x2 = meta.x2;
					userCoords.y2 = meta.y2;
	
					var duration = 1000;
					var anim = new Kinetic.Animation(function(frame) {
		                  if (frame.time >= duration) {
		                  	anim.stop();
		                  	isUserTankAnimated = false;
		                  } else{
			                tank.setX(userCoords.x1);
							tank.setY(userCoords.y1);
			                turret.setX(userCoords.x1);
							turret.setY(userCoords.y1);	                  
						  }
		                }, layer);
		            anim.start();
				}
			});	
		}

		function rotateTank(layer, tank, arguments, isClockwise){
			var url = "<?= base_url() ?>combat/postTankCoords";
			$.post(url,arguments, function (data,textStatus,jqXHR){
				meta = $.parseJSON(data);
				if (meta && meta.status=='success') {
						
					userCoords.x1 = meta.x1;
					userCoords.y1 = meta.y1;
					userCoords.x2 = meta.x2;
					userCoords.y2 = meta.y2;
	
	                var angularSpeed = Math.PI / 2;
	                var duration = 1000;
	                var sum = 0;
	                var anim = new Kinetic.Animation(function(frame) {
		                  if (frame.time > duration) {
		                    anim.stop();
		                    if(isClockwise){
								tank.rotate(Math.PI/2 - sum);
		                    } else{
								tank.rotate((-1)*(Math.PI/2 - sum));
		                    }
			                layer.draw();
			                isUserTankAnimated = false;
		                  } else{
		                    var angleDiff = frame.timeDiff * angularSpeed / 1000;
		                    if (isClockwise == 1){
			                    tank.rotate(angleDiff);
			                    sum += angleDiff;
		                    } else{
			                    tank.rotate((-1)*angleDiff);
			                    sum += angleDiff;
		                    }
		                  }
	                }, layer);
	
	                anim.start();
				}
			});	
		}

		function rotateTurret(layer, turret, arguments, isClockwise){
			var url = "<?= base_url() ?>combat/postTankCoords";
			$.post(url,arguments, function (data,textStatus,jqXHR){
				meta = $.parseJSON(data);
				if (meta && meta.status=='success') {
					userCoords.angle = meta.angle;
	
					var angularSpeed = Math.PI / 6;
					var duration = 1000;
					var sum = 0;
					var anim = new Kinetic.Animation(function(frame){
						if (frame.time > duration) {
		                    anim.stop();
		                    if(isClockwise){
								turret.rotate(Math.PI/6 - sum);
		                    } else{
								turret.rotate((-1)*(Math.PI/6 - sum));
		                    }
		                    layer.draw();
		                    isUserTankAnimated = false;
						} else {
							var angleDiff = frame.timeDiff * angularSpeed / 1000;
							if(isClockwise == 1){
								turret.rotate(angleDiff);
								sum += angleDiff;
							} else {
								turret.rotate((-1)*angleDiff);
								sum += angleDiff;
							}
						}
					}, layer);
	
					anim.start();
				}
			});
		}

		function shootBullet(layer, bullet, arguments){
			var url = "<?= base_url() ?>combat/postTankCoords";
			$.post(url,arguments, function (data,textStatus,jqXHR){
				meta = $.parseJSON(data);
				if (meta && meta.status=='success') {
					userCoords.shot = meta.shot;
					isUserTankAnimated = false;
					isBulletAnimating = true;
	                bullet.setX(parseInt(meta.x1));
					bullet.setY(parseInt(meta.y1));
					layer.draw();
					enemyHit = false;
					var anim = new Kinetic.Animation(function(frame) {
							if(enemyHit == true){
								anim.stop();
								isBulletAnimating = false;
								var url = "<?= base_url() ?>combat/postBattleStatus";
								$.post(url, function (data,textStatus,jqXHR){
									alert("Congratulations, you have won!");
									window.location.href = '<?= base_url() ?>arcade/index';
								});
							}
							else if (bullet.getPosition().x > 1000 || bullet.getPosition().x < 0
		    	                   || bullet.getPosition().y > 600 || bullet.getPosition().y < 0) {
			                  // bullet out of bounds or hit target -- stop
		                  	anim.stop();
		                  	isBulletAnimating = false;
		                  	bullet.setX(-100);
		                  	bullet.setY(-100);
		                  	arguments = {x1:userCoords.x1, y1:userCoords.y1, x2:userCoords.x2, 
		    	                  	y2:userCoords.y2, angle:userCoords.angle, shot:"0", hit:"0"};
		                  	$.post(url, arguments, function(data, textStatus, jqXHR){
								meta2 = $.parseJSON(data);
								if (meta2 && meta2.status=='success') {
									userCoords.shot = meta2.shot;
								}
		                  	});
		                  } else {
			                var angleRad = toRadians(parseInt(meta.angle)*30);
		                    bullet.setX(bullet.getPosition().x + (10*Math.sin(angleRad)));
		                    bullet.setY(bullet.getPosition().y - (10*Math.cos(angleRad)));
	
							var centerX = parseInt(otherUserCoords.x1);
							var centerY = parseInt(otherUserCoords.y1);
							var dir = parseInt(otherUserCoords.x2);
	
							if(dir == 0 || dir ==2){
								if((bullet.getPosition().x <= (centerX+25)) && (bullet.getPosition().x >= (centerX-25)) &&
									(bullet.getPosition().y <= (centerY+32)) && (bullet.getPosition().y >= (centerY-32))){
									enemyHit = true;
								}									
							} else{
								if((bullet.getPosition().x <= (centerX+32)) && (bullet.getPosition().x >= (centerX-32)) &&
									(bullet.getPosition().y <= (centerY+25)) && (bullet.getPosition().y >= (centerY-25))){
									enemyHit = true;
								}	
							}
		                  }
		            }, layer);
		            anim.start();
				}
			});
		}			

		function shootOtherBullet(layer, bullet, coords){
            bullet.setX(parseInt(coords.x1));
			bullet.setY(parseInt(coords.y1));	
			layer.draw();
			
			var anim = new Kinetic.Animation(function(frame) {
                  if (bullet.getPosition().x > 1000 || bullet.getPosition().x < 0
    	                   || bullet.getPosition().y > 600 || bullet.getPosition().y < 0) {
	                  // bullet out of bounds or hit target -- stop
                  	anim.stop();
                  	bullet.setX(-100);
                  	bullet.setY(-100);
                  	otherUserCoords.shot = 0;
                  } else {
	                var angleRad = toRadians(parseInt(coords.angle)*30);
                    bullet.setX(bullet.getPosition().x + (10*Math.sin(angleRad)));
                    bullet.setY(bullet.getPosition().y - (10*Math.cos(angleRad)));
                    }
                }, layer);
            anim.start();
		}

		function rotateOtherTank(layer, tank, isClockwise){
			angularSpeed = Math.PI / 2;
			var duration = 1000;
			var sum = 0;
			var anim = new Kinetic.Animation(function(frame){
				if(frame.time > duration) {
					anim.stop();
					if(isClockwise){
						tank.rotate(Math.PI/2 - sum);
					} else {
						tank.rotate((-1)*(Math.PI/2 - sum));
					}
					layer.draw();
				} else {
					var angleDiff = frame.timeDiff * angularSpeed / 1000;
					if (isClockwise == 1){
						tank.rotate(angleDiff);
						sum += angleDiff;
					} else {
						tank.rotate((-1)*angleDiff);
						sum += angleDiff;
					}
				}
			}, layer);

			anim.start();
		}

		function rotateOtherTurret(layer, turret, isClockwise){
			angularSpeed = Math.PI / 6;
			var duration = 1000;
			var sum = 0;
			var anim = new Kinetic.Animation(function(frame){
				if(frame.time > duration) {
					anim.stop();
					if(isClockwise){
						turret.rotate(Math.PI/6 - sum);
					} else {
						turret.rotate((-1)*(Math.PI/6 - sum));
					}
					layer.draw();
				} else {
					var angleDiff = frame.timeDiff * angularSpeed / 1000;
					if (isClockwise == 1){
						turret.rotate(angleDiff);
						sum += angleDiff;
					} else {
						turret.rotate((-1)*angleDiff);
						sum += angleDiff;
					}
				}
			}, layer);

			anim.start();
		}
		
		function drawTank(){
		        var stage = new Kinetic.Stage({
		          container: 'container',
		          x:0,
		          y:0,
		          width: 1000,
		          height: 600
		        });
		        var layer = new Kinetic.Layer();

		        /*
		         * USER'S TANK
		         * 
		         */

		        var imageObj = new Image();
		        imageObj.onload = function() {
		          var tank = new Kinetic.Image({
		            x: 0,
		            y: 0,
		            image: imageObj,
		            width: 50,
		            height: 64,
		            offset: [25, 32]
		          });

				  var imageObj3 = new Image();
				  	imageObj3.onload = function(){
						var turret = new Kinetic.Image({
							x: 0,
							y: 0,
							image: imageObj3,
							width: 42,
							height: 88,
							offset: [21, 44] 
						});

						var imageObj5 = new Image();
						  	imageObj5.onload = function(){
								var bullet = new Kinetic.Image({
									x: -100,
									y: -100,
									image: imageObj5,
									width: 24,
									height: 24,
									offset: [12, 12] 
								});
								
					      	  tank.rotateDeg(userCoords.x2*90);
					          tank.move(userCoords.x1, userCoords.y1);
					          turret.rotateDeg(userCoords.angle*30);
					          turret.move(userCoords.x1, userCoords.y1);
					          layer.add(tank);
					          layer.add(turret);
					          layer.add(bullet);
					          stage.add(layer);
			
					          
					          window.addEventListener('keydown', function(event) {
				        		var keyCode = event.keyCode || event.which;
					            var keyMap = { left:37, up:38, right:39, down:40, a:65, d:68, spacebar:32};
								var arguments = {x1:userCoords.x1, y1:userCoords.y1, x2:userCoords.x2, y2:userCoords.y2, angle:userCoords.angle, shot:userCoords.shot, hit:userCoords.hit};
								
							  	if(isUserTankAnimated == false){
									switch(keyCode){
										case keyMap.left:
											isUserTankAnimated = true;
											arguments.x2 = (parseInt(userCoords.x2) == 0) ?3 :(parseInt(userCoords.x2)-1);
											rotateTank(layer, tank, arguments, 0);
											break;
					
										case keyMap.right:
											isUserTankAnimated = true;
											arguments.x2 = (parseInt(userCoords.x2) == 3) ?0 :(parseInt(userCoords.x2)+1);
											rotateTank(layer, tank, arguments, 1);
											break;
					
										case keyMap.up:
											isUserTankAnimated = true;
											switch(userCoords.x2){
												case "0":
													var newY1 = parseInt(userCoords.y1) - 20;
													if(newY1 > 0 && newY1 <600){
														arguments.y1 = newY1; 
													}
													break;
												case "1":
													var newX1  = parseInt(userCoords.x1) + 20;
													if(newX1 > 0 && newX1 <1000){
														arguments.x1 = newX1; 
													}
													break;
												case "2":
													var newY1  = parseInt(userCoords.y1) + 20;
													if(newY1 > 0 && newY1 <600){
														arguments.y1 = newY1; 
													}
													break;
												case "3":
													var newX1  = parseInt(userCoords.x1) - 20;
													if(newX1 > 0 && newX1 <1000){
														arguments.x1 = newX1; 
													}
													break;
											}
											translateTank(layer, tank, arguments, turret);
											break;
											
										case keyMap.down:
											isUserTankAnimated = true;
											switch(userCoords.x2){
												case "0":
													var newY1  = parseInt(userCoords.y1) + 20;
													if(newY1 > 0 && newY1 <600){
														arguments.y1 = newY1; 
													}													
													break;
												case "1":
													var newX1  = parseInt(userCoords.x1) - 20;
													if(newX1 > 0 && newX1 <1000){
														arguments.x1 = newX1; 
													}													
													break;
												case "2":
													var newY1 = parseInt(userCoords.y1) - 20;
													if(newY1 > 0 && newY1 <600){
														arguments.y1 = newY1; 
													}
													break;	
												case "3":
													var newX1  = parseInt(userCoords.x1) + 20;
													if(newX1 > 0 && newX1 <1000){
														arguments.x1 = newX1; 
													}
													break;
											}
											translateTank(layer, tank, arguments, turret);
											break;
		
										case keyMap.a:
											isUserTankAnimated = true;
											arguments.angle = (parseInt(userCoords.angle) == 0) ?11 :(parseInt(userCoords.angle)-1);
											rotateTurret(layer, turret, arguments, 0);
											break;
				
										case keyMap.d:
											isUserTankAnimated = true;
											arguments.angle = (parseInt(userCoords.angle) == 11) ?0 :(parseInt(userCoords.angle)+1);
											rotateTurret(layer, turret, arguments, 1);
											break;
				
										case keyMap.spacebar:
											if(isBulletAnimating == false){
												isUserTankAnimated = true;
												arguments.shot = "1";
												shootBullet(layer, bullet, arguments);
											}
											break;	
									}
							  	}
						     });
				  		}
						imageObj5.src = "<?= base_url() ?>images/green-bullet.png";
				  };
				imageObj3.src = "<?= base_url() ?>images/green-turret.png";
			  };
			        
		        imageObj.src = "<?= base_url() ?>images/green-tank.png";

		        /*
		         * OTHER USER'S TANK
		         * 
		         */

		        var imageObj2 = new Image();
		        imageObj2.onload = function() {
		          var otherTank = new Kinetic.Image({
		            x: 0,
		            y: 0,
		            image: imageObj2,
		            width: 50,
		            height: 64,
		            offset: [25, 32]
		          });

				  var imageObj4 = new Image();
				  	imageObj4.onload = function(){
						var otherTurret = new Kinetic.Image({
							x: 0,
							y: 0,
							image: imageObj4,
							width: 42,
							height: 88,
							offset: [21, 44] 
				  	});

					var imageObj6 = new Image();
				  	imageObj6.onload = function(){
						var otherBullet = new Kinetic.Image({
							x: -100,
							y: -100,
							image: imageObj6,
							width: 24,
							height: 24,
							offset: [12, 12] 
						});
	
			          otherTank.move(-100, -100);
			          otherTurret.move(-100, -100);
			          layer.add(otherTank);
			          layer.add(otherTurret);
			          layer.add(otherBullet);
			          layer.draw();
	
	
			          $('#container').everyTime(1000, function() {
			        	  var url = "<?= base_url() ?>combat/getTankCoords";
							$.getJSON(url, function (data,text,jqXHR){
								console.log(data.status);
								if (data && data.status=='defeat'){
									alert(data.msg);
									window.location.href = '<?= base_url() ?>arcade/index';
								} else if (data && data.status=='success') {
									var coords = data.coords;
									if (coords.x1 != -1 && coords.y1 != -1 &&
										coords.x2 != -1 && coords.y2 != -1){
										
											if((coords.shot == 1 || coords.shot == "true") && (otherUserCoords.shot == 0 || otherUserCoords.shot == "false")){
												otherUserCoords.shot = coords.shot;

												shootOtherBullet(layer, otherBullet, coords);
											}
											
											otherUserCoords.x1 = coords.x1;
											otherUserCoords.y1 = coords.y1;
	
											if(isInitialRotation){
												otherUserCoords.x2 = coords.x2;
												otherUserCoords.angle = coords.angle;
												isInitialRotation = false;
												otherTank.rotateDeg(parseInt(coords.x2)*90);
												otherTurret.rotateDeg(parseInt(coords.angle)*30);
											} else {
												if(coords.x2 != otherUserCoords.x2){
													if(otherUserCoords.x2 == 3 && coords.x2 == 0){
														rotateOtherTank(layer, otherTank, 1);
													} else if (otherUserCoords.x2 == 0 && coords.x2 == 3){
														rotateOtherTank(layer, otherTank, 0);
													} else if(otherUserCoords.x2 > coords.x2){
														rotateOtherTank(layer, otherTank, 0);
													} else {
														rotateOtherTank(layer, otherTank, 1);
													}
													otherUserCoords.x2 = coords.x2;
												}
												if(coords.angle != otherUserCoords.angle){
													if(otherUserCoords.angle == 11 && coords.angle == 0){
														rotateOtherTurret(layer, otherTurret, 1);
													} else if (otherUserCoords.angle == 0 && coords.angle == 11){
														rotateOtherTurret(layer, otherTurret, 0);
													} else if(parseInt(otherUserCoords.angle) > parseInt(coords.angle)){
														rotateOtherTurret(layer, otherTurret, 0);
													} else {
														rotateOtherTurret(layer, otherTurret, 1);
													}
													otherUserCoords.angle = coords.angle;												
												}
											}
	
											// translation for other tank and other turret
											otherTank.setX(otherUserCoords.x1);
											otherTank.setY(otherUserCoords.y1);
											otherTurret.setX(otherUserCoords.x1);
											otherTurret.setY(otherUserCoords.y1);
											layer.draw();
									}
								}
							});
			          });
			  		}
					imageObj6.src = "<?= base_url() ?>images/red-bullet.png";
				  }
			     imageObj4.src = "<?= base_url() ?>images/red-turret.png";
					
			  };

		        imageObj2.src = "<?= base_url() ?>images/red-tank.png";
		}
			
		function tankCoords(){
			this.x1 = -1;
			this.y1 = -1;
			this.x2 = -1;
			this.y2 = -1;
			this.angle = -1;
			this.shot = 0;
			this.hit = 0;
		}
		
		var otherUser = "<?= $otherUser->login ?>";
		var user = "<?= $user->login ?>";
		var status = "<?= $status ?>";
		var userCoords = new tankCoords();
		var otherUserCoords = new tankCoords();
		var isUserTankAnimated = false;
		var isInitialRotation = true;
		var isBulletAnimating = false;
		var isOtherBulletAnimating = false;

		
		$(function(){

			$(window).load(function(){
				// set up canvas for player who accepted the battle invite 
				if (status == 'battling'){
					
					// update tank coords
					var arguments = {x1:'75', y1:'420', x2:'0', y2:'430', angle:'0', shot:false, hit:false};
					var url = "<?= base_url() ?>combat/postTankCoords";
					$.post(url,arguments, function (data,textStatus,jqXHR){
						//invitee
						meta = $.parseJSON(data);
						if (meta && meta.status=='success') {
							userCoords.x1 = meta.x1;
							userCoords.y1 = meta.y1;
							userCoords.x2 = meta.x2;
							userCoords.y2 = meta.y2;
							userCoords.angle = meta.angle;
							userCoords.shot = meta.shot;
							userCoords.hit = meta.hit;
							
							// set up canvas for player whose battle invite got accepted
							drawTank();
						}
					});
				}
			});
			
			$('body').everyTime(1000,function(){
					if (status == 'waiting') {
						$.getJSON('<?= base_url() ?>arcade/checkInvitation',function(data, text, jqZHR){
								if (data && data.status=='rejected') {
									alert("Sorry, your invitation to battle was declined!");
									window.location.href = '<?= base_url() ?>arcade/index';
								}
								if (data && data.status=='accepted') {
									status = 'battling';
									
									$('#status').html('Battling ' + otherUser);
									//inviter
									//update tanks coords
									var arguments = {x1:'900', y1:'45', x2:'2', y2:'20', angle:'6', shot:false, hit:false};
									var url = "<?= base_url() ?>combat/postTankCoords";
									$.post(url,arguments, function (data,textStatus,jqXHR){
										//inviter
										meta = $.parseJSON(data);
										if (meta && meta.status=='success') {
											userCoords.x1 = meta.x1;
											userCoords.y1 = meta.y1;
											userCoords.x2 = meta.x2;
											userCoords.y2 = meta.y2;
											userCoords.angle = meta.angle;
											userCoords.shot = meta.shot;
											userCoords.hit = meta.hit;
											
											// set up canvas for player whose battle invite got accepted
											drawTank();
										}
									});
								}
								
						});
					} 
									
					var url = "<?= base_url() ?>combat/getMsg";
					$.getJSON(url, function (data,text,jqXHR){
						if (data && data.status=='success') {
							var conversation = $('[name=conversation]').val();
							var msg = data.message;
							if (msg.length > 0)
								$('[name=conversation]').val(conversation + "\n" + otherUser + ": " + msg);
						}
					});
			});
			
			$('form').submit(function(){
				var arguments = $(this).serialize();
				var url = "<?= base_url() ?>combat/postMsg";
				$.post(url,arguments, function (data,textStatus,jqXHR){
						var conversation = $('[name=conversation]').val();
						var msg = $('[name=msg]').val();
						$('[name=conversation]').val(conversation + "\n" + user + ": " + msg);
				});
			return false;
			});	
		});
	</script>
	</head> 
	
	<body>  		
		<div id = "canvasContent">
			<div id = "canvasContainer">				
				<div id="container" style = "background-color:black; width:1000px; height:600px; "></div>
			
				<div id = "stuffContainer">
					<div id = "hello">
					Hello <?= $user->fullName() ?>  <?= anchor('account/logout','(Logout)') ?>  <?= anchor('account/updatePasswordForm','(Change Password)') ?>
					</div>
					
					<div id='status'> 
					<?php 
						if ($status == "battling")
							echo "Battling " . $otherUser->login;
						else
							echo "Waiting on " . $otherUser->login;
					?>
					</div>
					
					<?php 
						echo form_textarea('conversation');
						echo form_open();
						echo form_input('msg');
						echo form_submit('Send','Send');
						echo form_close();
						
					?>
				</div>
			</div>	
		</div>
	</body>

</html>

