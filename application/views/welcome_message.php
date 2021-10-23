<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title>
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<style>
		.form-error{
			color: red
		}
		.modal{
			/* display: block; */
		}
	</style>
</head>
<body>
	<div id="app">


	<div class="modal fade" id="rfidScanModal">
		<div class="modal-dialog">
			<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Modal title</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form @submit.prevent="addRfid" id="rfid-form">
					<div class="mb-3">
						<label for="rfid" class="form-label">RFID</label>
						<input type="text" class="form-control" v-model="formdata.rfid" ref="input" autofocus> 
						<div class="form-text form-error">{{ formerror.rfid }}</div>
					</div>
					<div class="mb-3">
						<label for="rfid_expiry" class="form-label">Date Expiry</label>
						<input type="date" class="form-control" v-model="formdata.rfid_expiry">
						<div class="form-text form-error">{{ formerror.rfid_expiry }}</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" ref="close">Close</button>
				<button type="submit" class="btn btn-primary" form="rfid-form">Save changes</button>
			</div>
			</div>
		</div>
	</div>

	
	<div class="container">
		<nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <a class="navbar-brand" href="#"></a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
					<li class="nav-item">
						<a class="nav-link active" aria-current="page" href="/">Clients</a>
                    </li>
                    <li class="nav-item">
						<a class="nav-link" aria-current="page" href="/gate">Gate</a>
                    </li>
                </ul>
                </div>
            </div>
        </nav>
		<br>
		<div class="row">
			<div class="col-md-3">
				<h3 style="text-align:center">{{ formtype == 'create' ? "Add" : "Edit" }} Client</h3>
				<form @submit.prevent="submitClientForm">
					<div class="mb-3">
						<label for="color" class="form-label">Color</label>
						<input type="text" class="form-control" v-model="formdata.color">
						<div class="form-text form-error">{{ formerror.color }}</div>
					</div>
					<div class="mb-3">
						<label for="plate_number" class="form-label">Plate number</label>
						<input type="text" class="form-control" v-model="formdata.plate_number">
						<div class="form-text form-error">{{ formerror.plate_number }}</div>
					</div>
					<div class="mb-3">
						<label for="model" class="form-label">Model</label>
						<input type="text" class="form-control" v-model="formdata.model">
						<div class="form-text form-error">{{ formerror.model }}</div>
					</div>
					<div class="mb-3">
						<label for="brand" class="form-label">Brand</label>
						<input type="text" class="form-control" v-model="formdata.brand">
						<div class="form-text form-error">{{ formerror.brand }}</div>
					</div>
					<button type="submit" class="btn btn-primary">Save</button>
					<button type="button" class="btn btn-danger" @click="clearForm">Clear</button>
				</form>
			</div>
			<div class="col-md-9">
				<h3 style="text-align:center">Clients</h3>
				<table class="table table-bordered">
					<thead>
						<tr>
							<th>Color</th>
							<th>Plate number</th>
							<th>Model</th>
							<th>Brand</th>
							<th>RFID</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="item in clients">
							<td>{{ item.color }}</td>
							<td>{{ item.plate_number }}</td>
							<td>{{ item.model }}</td>
							<td>{{ item.brand }}</td>
							<td>
								<span v-if="item.rfid == null || item.rfid == '' ">
									<a href="javascript:void(0)" @click="scanRfid(item)" data-bs-toggle="modal" data-bs-target="#rfidScanModal">SCAN</a>
								</span>
								<span v-else>
									<span>RFID: <b>{{ item.rfid }}</b></span><br>
									<span>Valid Until: <b>{{ item.rfid_expiry }}</b></span><br>
									<a href="javascript:void(0)" @click="removeRfid(item.id)">Remove RFID</a>
								</span>
							</td>
							<td>
								<div class="btn-group" role="group" aria-label="Basic example">
									<button class="btn btn-primary" @click="editClient(item)">Edit</button>
									<button class="btn btn-danger" @click="deleteClient(item.id)">Delete</button>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>


	</div>
<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/vue.js"></script>
<script src="js/axios.min.js"></script>
<script src="js/lodash.js"></script>
<script>
	var rfidScanModal = new bootstrap.Modal(document.getElementById('rfidScanModal'), {
		keyboard: false
	})
    var app = new Vue({
        el: '#app',
        data: {
            formdata: {},
			formerror: {},
			formtype: "create",
			clients: [],
			selectedClient: {}
        },
		methods: {
			submitClientForm(){
				if (this.formtype == "create") {
					this.addClient();
				}else{
					this.addRfid("updateClient");
				}
			},
			addClient(){
				axios.post('/client', this.serialize(this.formdata), {
					headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'}
				})
				.then((response) => {
					this.getClients();
					this.clearForm()
				})
				.catch((error) => {
					this.formerror = error.response.data;
				});
			},
			addRfid(type = "rfid"){
				let formdata = _.clone(this.selectedClient);
				formdata.rfid = this.formdata.rfid? this.formdata.rfid : "";
				formdata.rfid_expiry = this.formdata.rfid_expiry? this.formdata.rfid_expiry : "";
				formdata.type = type;
				axios.post(`/client-update/${this.selectedClient.id}`, this.serialize(formdata), {
					headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'}
				})
				.then((response) => {
					this.getClients();
					this.clearForm()
					this.$refs.close.click()
				})
				.catch((error) => {
					this.formerror = error.response.data;
				});
			},

			removeRfid(id){
				axios.post(`/remove-rfid/${id}`)
				.then((response) => {
					this.getClients();
				})
				.catch((error) => {
					this.formerror = error.response.data;
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

			getClients(){
				axios.get('/client')
				.then((response) => {
					this.clients = response.data
				})
				.catch((error) => {

				});
			},

			deleteClient(id){
				axios.delete(`/client/${id}`)
				.then((response) => {
					this.getClients();
				})
				.catch((error) => {

				});
			},

			editClient(client){
				this.formdata = client;
				this.formtype = "update";
				this.selectedClient = client;
			},

			clearForm(){
				this.formdata = [];
				this.formtype = "create";
			},

			scanRfid(selectedClient){
				this.$refs.input.focus()
				let d = new Date();
				d.setDate(d.getDate() + 360); 
				let dt = d.toISOString();
				this.formdata = {
					rfid_expiry: dt.substring(0,10)
				};
				this.selectedClient = selectedClient;
			}
		},
		mounted(){
			this.getClients();
		}
    })
</script>
</body>
</html>