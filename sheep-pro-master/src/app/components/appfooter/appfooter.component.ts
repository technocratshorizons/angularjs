import { Component, OnInit } from '@angular/core';

@Component({
  selector: 'appfooter',
  templateUrl: './appfooter.component.html',
  styleUrls: ['./appfooter.component.css']
})
export class AppfooterComponent implements OnInit {
	dates = new Date();
  constructor() { }

  ngOnInit() {
	console.log(this.dates);
  }

}
