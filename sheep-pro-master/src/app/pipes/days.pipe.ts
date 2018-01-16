import { Pipe, PipeTransform } from '@angular/core';
import { DomSanitizer} from '@angular/platform-browser';

//Pipe For get Joining Days
@Pipe({name: 'joiningDays'})
export class joiningDays implements PipeTransform {
  transform(obje: any, before: object): number {
  	// console.log('before',before);
    var dateone: any = new Date(before[0]); 
	var datetwo: any = new Date(before[1]);
	var dayDif = (datetwo - dateone)  / 1000 / 60 / 60 / 24;
  dayDif = dayDif+1;
	obje.setValue(dayDif);
    return dayDif;
  }
}

//Pipe for get Introduced days
@Pipe({name: 'introducedDays'})
export class introducedDays implements PipeTransform {
  transform(obje: any, value: object, bookingDate: any): number {
  var roomInDate: any = new Date(value[0]); 
  // var roomOutDate: any = new Date(value[1]); 
	var todayDate: any = new Date(bookingDate[0]);
    todayDate.setHours(0);
    todayDate.setMinutes(0);
    todayDate.setSeconds(0);

    roomInDate.setHours(0);
    roomInDate.setMinutes(0);
    roomInDate.setSeconds(0);

	var dayDif =  Math.round((todayDate - roomInDate)  / 1000 / 60 / 60 / 24);

    if(bookingDate[0] != '' && bookingDate[0] != undefined){
       if(dayDif == -1 || dayDif == 0){
         dayDif = 1;
       }
       else if(dayDif<0 && dayDif!=-1){
            dayDif = dayDif;
        }else{
            dayDif = dayDif+1;
        }
    } else {
        dayDif = 0;
    }
	obje.setValue(dayDif);
    return dayDif;
  }
}

//Pipe for get free days
@Pipe({name: 'roomFreeDays'})
export class roomFreeDays implements PipeTransform {
  transform(obje: any, value: object, bookingDate: any): number {
   var roomOutDate: any = new Date(value[1]); 
	var todayDate: any = new Date(bookingDate[0]);

     todayDate.setHours(0);
    todayDate.setMinutes(0);
    todayDate.setSeconds(0);

    roomOutDate.setHours(0);
    roomOutDate.setMinutes(0);
    roomOutDate.setSeconds(0);


	var dayDif = Math.round((todayDate - roomOutDate)  / 1000 / 60 / 60 / 24);
   
    if(bookingDate[0] != '' && bookingDate[0] != undefined){
       if(dayDif == -1 || dayDif == 0){
         dayDif = 1;
       }
       else if(dayDif<0 && dayDif!=-1){
            dayDif = dayDif;
        }else{
            dayDif = dayDif+1;
        }
    } else {
        dayDif = 0;
    }
    obje.setValue(dayDif);
        return dayDif;
    }
}


//Pipe for get scanning date
@Pipe({name: 'scanDate'})
export class scanDate implements PipeTransform {
  transform(obje: any, date: object, type: string): Date {

    if(type=='Single') {
      var dt = new Date(date[1]);
      dt.setDate(dt.getDate() + 45);
    }
    else {
      var dt = new Date(date[0]);
      dt.setDate(dt.getDate() + 84);
    }
    obje.setValue(dt);
    return dt;
  }
}

@Pipe({name: 'idealDays'})
export class idealDays implements PipeTransform {
  transform(obje: any, type: string): Number {

    if(type=='Single') {
      var sm = 45;
    }
    else {
      var sm = 84;
    }
    obje.setValue(sm);
    return sm;
  }
}



@Pipe({ name: 'safe' })
export class SafePipe implements PipeTransform {
  constructor(private sanitizer: DomSanitizer) {}
  transform(url) {
    return this.sanitizer.bypassSecurityTrustResourceUrl(url);
  }
} 