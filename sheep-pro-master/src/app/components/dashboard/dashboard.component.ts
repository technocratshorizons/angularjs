import { Component, OnInit } from '@angular/core';
import { LaddaModule } from 'angular2-ladda';
import { UserService, NotficationService } from '../../_services/index';

@Component({
	selector: 'app-dashboard',
	templateUrl: './dashboard.component.html',
	styleUrls: ['./dashboard.component.css']
})
export class DashboardComponent implements OnInit {
	_session = JSON.parse(localStorage.getItem('currentUser'));
	clients:number;
	scanners:number;
	admins:number;
	bookings:number;
	constructor(private userService:UserService,private notficationService:NotficationService) { }

	ngOnInit() {

		this.userService.getAllUsersCount().subscribe(
			data => {
				if(data.status) {
					this.clients = data.clients;
					this.scanners = data.scanners;
					this.admins = data.admins;
					this.bookings = data.bookings;
				}
			},
			error => {
				this.notficationService.msg('error','Error!',error);
		});
	}
}
