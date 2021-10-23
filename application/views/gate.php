<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title>
	<link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
	<div class="container" id="app">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="#"></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
						<a class="nav-link" aria-current="page" href="/">Clients</a>
                    </li>
                    <li class="nav-item">
						<a class="nav-link active" aria-current="page" href="/gate">Gate</a>
                    </li>
                </ul>
                </div>
            </div>
        </nav>
        <br>
		<div class="row">
            <div class="col-md-12">
            <h3 style="text-align:center">Gate</h3>
                RFID: <input type="text" ref="input" @keyup.enter="scanRfid" v-model="rfid" autofocus>
                <div class="alert alert-success" role="alert" v-show="rfidScannedStatus == 'success'">
                    Color: {{ scannedResult?.client?.color }}<br>
                    Brand: {{ scannedResult?.client?.brand }}<br>
                    Model: {{ scannedResult?.client?.model }}<br>
                    Plate Number: {{ scannedResult?.client?.plate_number }}<br>
                    <strong>{{ scannedResult.type == 'entry' ? "Entered" : "Exited" }} the premises on {{ scannedResult.date_passed }}</strong>
                    <!-- <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" ref="successClose"></button> -->
                </div>
                <div class="alert alert-danger" role="alert" v-show="rfidScannedStatus == 'error'">
                    <strong>Not registered RFID</strong>
                    <!-- <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" ref="errorClose"></button> -->
                </div>
                <br>
                <br>
                Search: <input type="search" v-model="search.searchstring">
                Date: <input type="date" v-model="search.date_passed">
                <button class="btn btn-primary" @click="getGateLogs">Search</button>
                <button class="btn btn-info" @click="downloadGatelog">Download</button>
                <br>
                <br>
				<table class="table table-bordered">
					<thead>
						<tr>
							<th>Type</th>
							<th>Date Time</th>
							<th>Color</th>
							<th>Plate number</th>
							<th>Model</th>
							<th>Brand</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="item in gatelogs" :class="[item.type == 'entry' ? 'table-success' : 'table-danger']">
							<td>{{ item.type }}</td>
							<td>{{ item.created_at }}</td>
							<td>{{ item.color }}</td>
							<td>{{ item.plate_number }}</td>
							<td>{{ item.model }}</td>
							<td>{{ item.brand }}</td>
						</tr>
					</tbody>
				</table>
            </div>
		</div>
	</div>
<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/vue.js"></script>
<script src="js/axios.min.js"></script>
<script src="js/lodash.js"></script>
<script>
    var alertNode = document.querySelector('.alert')
    var alert = bootstrap.Alert.getInstance(alertNode)
    var timerId;
    var app = new Vue({
        el: '#app',
        data: {
            rfid: "",
            gatelogs: [],
            clients: [],
            rfidScannedStatus: "",
            search: {},
            scannedResult: {}
        },
        methods: {
            scanRfid(){
                clearTimeout(timerId);
                axios.post(`/scan-rfid`, this.serialize({rfid: this.rfid}), {
					headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'}
				})
				.then((response) => {
                    clearInterval(0)
                    this.getGateLogs();
                    this.rfid = "";
                    this.rfidScannedStatus = "success";
                    this.scannedResult = response.data;
                    timerId = setTimeout(() => {
                        this.rfidScannedStatus = "";
                        // this.$refs.successClose.click()
                    }, 5000);
				})
				.catch((error) => {
                    clearInterval(0)
                    this.rfidScannedStatus = "error";
                    this.rfid = "";
                    timerId = setTimeout(() => {
                        this.rfidScannedStatus = "";
                        // this.$refs.errorClose.click()
                    }, 5000);

				});
            },
            serialize(obj){
				var str = [];
				for (var p in obj)
					if (obj.hasOwnProperty(p)) {
					str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
					}
				return str.join("&");
			},

            searchGatelog(){

            },

            getGateLogs(){
                let params = this.search;
				axios.get('/gate/logs', {params})
				.then((response) => {
					this.gatelogs = response.data
				})
				.catch((error) => {

				});
			},

            downloadGatelog(){
                axios.get('/gate-logs-download')
				.then((response) => {
					window.location = '/gatelogs.csv';
				})
				.catch((error) => {

				});
            },

            getClients(){
				axios.get('/client')
				.then((response) => {
					this.clients = response.data
				})
				.catch((error) => {

				});
			},

        },
        mounted(){
			this.getGateLogs();
			this.getClients();
            this.$refs.input.focus()
		}
    })
</script>
</body>
</html>