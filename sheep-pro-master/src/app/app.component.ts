import { Component } from '@angular/core';
import {NgZone, Renderer, ElementRef, ViewChild} from '@angular/core'
import {Router, Event as RouterEvent, NavigationStart, NavigationEnd, NavigationCancel, NavigationError } from '@angular/router';
declare var moment:any;
@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent {
  @ViewChild('spinnerElement')
  spinnerElement: ElementRef
  

  loading:boolean = false;
  title = 'app';
	
  public options = {
    position: ["top", "right"],
    timeOut: 5000,
    lastOnBottom: true,
    preventDuplicates:true,
   	maxStack:1
	}

  constructor(private router: Router,private ngZone: NgZone,
              private renderer: Renderer){
    router.events.subscribe((event: RouterEvent) => {
     // console.log(event);
      this._navigationInterceptor(event)
    })
  }
 

   // Shows and hides the loading spinner during RouterEvent changes
   _navigationInterceptor(event: RouterEvent): void {
    if (event instanceof NavigationStart) {
      this._showSpinner()
    }
    if (event instanceof NavigationEnd) {
      this._hideSpinner()
    }
    // Set loading state to false in both of the below events to
    // hide the spinner in case a request fails
    if (event instanceof NavigationCancel) {
      this._hideSpinner()
    }
    if (event instanceof NavigationError) {
      this._hideSpinner()
    }
  }

   _hideSpinner(): void {
       var nZone = this.ngZone;
       var rendr = this.renderer;
       var spiner = this.spinnerElement;
       setTimeout(function(){ 
    // We wanna run this function outside of Angular's zone to
    // bypass change detection,
    nZone.runOutsideAngular(() => {
      // For simplicity we are going to turn opacity on / off
      // you could add/remove a class for more advanced styling
      // and enter/leave animation of the spinner
      rendr.setElementStyle(
        spiner.nativeElement,
        'display',
        'none'
      )
    })
        }, 2000);
  }

     _showSpinner(): void {
        // We wanna run this function outside of Angular's zone to
        // bypass change detection,
        this.ngZone.runOutsideAngular(() => {
          // For simplicity we are going to turn opacity on / off
          // you could add/remove a class for more advanced styling
          // and enter/leave animation of the spinner
          this.renderer.setElementStyle(
            this.spinnerElement.nativeElement,
            'display',
            'block'
          )
        })
    }
}
