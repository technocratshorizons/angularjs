import { Component, Input } from '@angular/core';
import { FormGroup } from '@angular/forms';
import { BsDatepickerConfig } from 'ngx-bootstrap/datepicker';

@Component({
    moduleId: module.id,
    selector: 'joining',
    templateUrl: 'joining.component.html'
})
export class JoiningComponent {
	bsConfig = Object.assign({}, { containerClass: 'theme-blue', dateInputFormat: 'DD-MM-YYYY' });
    bsRangeValue: any = [new Date(), new Date()];

    bsRangesValue: any = [new Date(), new Date()];
    // we will pass in address from App component
    @Input('group')
    public joiningForm: FormGroup;

    @Input() scanners:object;
    // options =  this.scanners;
    demo(value1="",value2=""){
    
    }
}