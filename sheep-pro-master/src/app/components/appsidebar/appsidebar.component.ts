import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'appsidebar',
  templateUrl: './appsidebar.component.html',
  styleUrls: ['./appsidebar.component.css']
})
export class AppsidebarComponent implements OnInit {
	_session  = JSON.parse(localStorage.getItem('currentUser'));
  constructor() { }
  ngOnInit() {
  	// this.popitup();
  }

  popitup() {
  	var w = window.open("https://www.google.com/calendar?tab=mc&authuser=1","_blank",
        'menubar=no,toolbar=no,location=no,directories=no,status=no,scrollbars=no,resizable=no,dependent,width=800,height=620,left=0,top=0');
  		return w?false:true; // allow the link to work if popup is blocked
	}
}
	