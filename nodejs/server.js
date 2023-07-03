const express = require('express');
const http = require('http');
const socketIO = require('socket.io');
const redis = require('redis');
const axios = require('axios');

const app = express();
const server = http.createServer(app);
const port = process.env.PORT || 8990;
const io = socketIO(server);

server.listen(port, () => console.log(`Server is running on port ${port}...`));

var redisClient = redis.createClient();

//subscribe to newRequest
redisClient.subscribe(['newRequest', 'checkTransportRequest', 'checkOrderRequest', 'checkServiceRequest', 'providerUpdate', 'settingsUpdate', 'paymentUpdate']);

redisClient.on("message", function(channel, data) {

	var data = JSON.parse(data);
	//Common request for admin users
	if(channel == 'newRequest')  {
		io.sockets.in(data.room).emit('newRequest', `New request created in common ${data.room}`);

		//Common request for providers based on city
		if(data.city != "") {
			var provider_room = `${data.room}_${data.city}`;
			io.sockets.in(provider_room).emit('newRequest', `New request created for providers in ${provider_room}`);
		} else if(data.city == 0) {
			var provider_room = `${data.room}_${data.city}`;
			io.sockets.in(provider_room).emit('newRequest', `New request created for providers in ${provider_room}`);
		}

		//Common request for providers based on user
		if(data.user != "") {
			var user_room = `${data.room}_${data.user}_USER`;
			io.sockets.in(user_room).emit('newRequest', `New request created for user in ${user_room}`);
		}

		//Common request for Shops based on city
		if(data.shop != "" && data.type == "ORDER") { 
			var shop_room = `${data.room}_shop_${data.shop}`;
			io.sockets.in(shop_room).emit('newRequest', `New shop request created in ${shop_room}`);
		}
	}

	

	//Common request for Shops based on city
	if(channel == 'providerUpdate')  {
		var provider_room = data.room;
		io.sockets.in(provider_room).emit('approval', `New document request created in ${provider_room}`);
	}

	if(channel == 'paymentUpdate')  {
		var room = `${data.room}_R${data.id}_${data.type}`;
		var nodeName;
		if(data.type == "TRANSPORT") {
			nodeName = 'rideRequest';
		} else if(data.type == "SERVICE") {
			nodeName = 'serveRequest';
		}

		io.sockets.in(room).emit(nodeName, {'payment_mode': data.payment_mode});
		
	}

	if(channel == 'settingsUpdate')  {
		//Settings Change
		if(data.type == "SETTING") {
			io.emit('settingUpdate', `Settings updated`);
		}

		//Settings Change
		if(data.type == "SERVICE_SETTING") {
			io.emit('serviceSettingUpdate', `Settings updated`);
		}
	}
	
	if(channel == 'checkTransportRequest')  {
		//Ride request
		if(data.type == "TRANSPORT") {
			var room = `${data.room}_R${data.id}_${data.type}`;
			io.sockets.in(room).emit('rideRequest', `New ride request created in ${room}`);
			var rooms = io.sockets.adapter;
		}
	}

	if(channel == 'checkServiceRequest')  {
		//Service Request
		if(data.type == "SERVICE") {
			var room = `${data.room}_R${data.id}_${data.type}`;
			io.sockets.in(room).emit('serveRequest', `New service request created in ${room}`);
		}
	}

	if(channel == 'checkOrderRequest')  { 
		//Order Request
		if(data.type == "ORDER") {
			var room = `${data.room}_R${data.id}_${data.type}`; 
			io.sockets.in(room).emit('orderRequest', `New Food request created in ${room}`);
		}
	}
	
});





io.sockets.on('connection', function(socket) {

	socket.on('joinCommonRoom', function(newroom) {

		var rooms = io.sockets.adapter.sids[socket.id];
		for(var room in rooms) { 
			socket.leave(room); 
		}
		
		socket.join(newroom);
		io.sockets.in(newroom).emit('socketStatus', 'you are connected to common '+ newroom);

	});

	socket.on('joinCommonProviderRoom', function(newroom) {
		
		socket.join(newroom);
		io.sockets.in(newroom).emit('socketStatus', 'you are connected to common '+ newroom);

	});

	socket.on('joinCommonUserRoom', function(newroom) {
		var rooms = io.sockets.adapter.sids[socket.id];
		for(var room in rooms) { 
			socket.leave(room); 
		}
		
		socket.join(newroom);
		io.sockets.in(newroom).emit('socketStatus', 'you are connected to common user '+ newroom);

	});

	socket.on('joinShopRoom', function(newroom) {
		var rooms = io.sockets.adapter.sids[socket.id];
		for(var room in rooms) { 
			socket.leave(room); 
		}

		socket.join(newroom);

		io.sockets.in(newroom).emit('socketStatus', 'you are connected to shop '+ newroom);

	});

	socket.on('joinPrivateRoom', function(newroom) {
		var rooms = io.sockets.adapter.sids[socket.id];
		for(var room in rooms) { 
		  socket.leave(room); 
		}

		socket.join(newroom);

		io.sockets.in(newroom).emit('socketStatus', 'you are connected to private '+ newroom);

	});

	socket.on('joinPrivateChatRoom', function(newroom) {

		socket.join(newroom);

		io.sockets.in(newroom).emit('socketStatus', 'you are connected to private chat '+ newroom);

	});

	socket.on('leaveRoom', function(newroom) {
		var rooms = io.sockets.adapter.sids[socket.id];
		for(var room in rooms) { 
			if(room == newroom) {
				socket.leave(room); 
			}
		}
	});

	socket.on('send_location', function(data) {
		io.sockets.in(data.room).emit('socketStatus', 'you are receiving message in '+ data.room);
		io.sockets.in(data.room).emit('updateLocation', {lat: data.latitude, lng: data.longitude});
	});

	socket.on('update_location', function(data) {
		io.sockets.in(data.room).emit('socketStatus', 'you are receiving message in '+ data.room);

		axios.post(data.url, {
			provider_id: data.provider_id,
			latitude: data.latitude,
			longitude: data.longitude
		})
		.then(response => {
			//console.log(response);
		})
		.catch(error => {
			//console.log(error);
		});

	});

	socket.on('send_message', function(data) {
		io.sockets.in(data.room).emit('socketStatus', 'you are receiving message in '+ data.room);
		io.sockets.in(data.room).emit('new_message', {type: data.type, message: data.message, user: data.user, provider: data.provider});

		axios.post(data.url, {
			id: data.id,
			admin_service: data.admin_service,
			salt_key: data.salt_key,
			user_name: data.user,
			provider_name: data.provider,
			type: data.type,
			message: data.message
		})
		.then(response => {
			//console.log(response);
		})
		.catch(error => {
			//console.log(error);
		});
	});

	socket.on('disconnect', function() {

	});

});
