import { Component, OnInit } from '@angular/core';
import { UserService, NotficationService} from '../../_services/index';
declare var jQuery: any;
declare var moment:any;
@Component({
    selector: 'app-calendar',
    templateUrl: './calendar.component.html',
    styleUrls: ['./calendar.component.css']
})
export class CalendarComponent implements OnInit {
    _session  = JSON.parse(localStorage.getItem('currentUser'));
    scanners: any;
    selected_calendar:any;
    num = 0;
    iframe:any;
    constructor(private userService: UserService,
    private notficationService: NotficationService) { }
  //   onLoadFunc(myIframe) {
  //       this.iframe = myIframe;
  //       if(this.num==0){
  //          this.num++;
  //          this.selected_calendar = jQuery('#scanner_type').val();
  //          this.setSource(this.selected_calendar);
  //       }
  // }

    ngOnInit() {
        this.userService.activeUsers({'users':['Scanner']}).subscribe(
        data => {
            if(data.status) {
                this.scanners = data.Scanner;
                 let self = this;
                setTimeout(() => { 
                    self.selected_calendar = jQuery('#scanner_type').val();
                    self.setSource(self.selected_calendar);
                    jQuery('body').on('change','#scanner_type', function(){
                        self.selected_calendar = jQuery(this).val();
                        self.setSource(self.selected_calendar);
                    });
                },1000);
            }
        },
        error => {
            this.notficationService.msg('error','Error!',error);
        });
    }

    setSource(elemt){
        var url = 'https://calendar.google.com/calendar/embed?height=600&wkst=1&bgcolor=%23FFFFFF&src='+elemt+'&ctz=Australia%2FSydney';
        var $iframe = jQuery('iframe');
        if ( $iframe.length ) {
            $iframe.attr('src',url);   
            return false;
        }
    }
}
