import { Component, OnInit } from '@angular/core';
import { UserService, NotficationService, BookingService } from '../../../_services/index';
import { Validators, FormGroup, FormArray, FormBuilder, FormControl } from '@angular/forms';
import { Joining } from '../../../joining.interface';
import { Router, ActivatedRoute } from '@angular/router';	
import { BsDatepickerConfig } from 'ngx-bootstrap/datepicker';
declare var jQuery: any;
@Component({
    selector: 'app-bookingadd',
    templateUrl: './bookingadd.component.html',
    styleUrls: ['./bookingadd.component.css']
})

export class BookingaddComponent implements OnInit {
    isLoading: boolean = false;
    clients:object;
    scanners:object;
    bsConfig = Object.assign({}, { containerClass: 'theme-blue' });
    public myForm: FormGroup; 
    constructor(private userService: UserService,
        private notficationService: NotficationService,
        private _fb: FormBuilder,
        private bookingService: BookingService,
        private router :Router
        ) { }

    ngOnInit() {
        this.initForm();
        var my_form = this.myForm;

        //Get Clients Here
        jQuery('.selectpicker2').selectpicker();
        this.userService.activeUsers({'users':['Client','Scanner']}).subscribe(
        data => {
            this.isLoading =false;
            if(data.status) {
                this.clients = data.Client;
                this.scanners = data.Scanner;
                 setTimeout(() => {
                   jQuery('.selectpicker2').selectpicker('refresh');
                   jQuery('.unavailable_dates').datepicker({
                        multidate: true,
                        format: 'dd-mm-yyyy'
                    }).on('changeDate',function(e){
                        my_form.controls.unavailable_dates.setValue(jQuery('.unavailable_dates').val())
                    })
               }, 150);
            }
        },
        error => {
            this.notficationService.msg('error','Error!',error);
        });
        //End here
    }


    ChangeEvent(argument) {
    }


    initForm(){
        //Set Form Builder
        this.myForm =  this._fb.group({
              client_details: ['',Validators.required],
              unavailable_dates: [''],
              client_confirmation_email: [''],
              client_confirmation_phone: [''],
              notes: [''],
              joinings_array: this._fb.array([
                    this.initJoining()
                  ])
        })
        //End here
    }


    initJoining(){
        //Initialise  our adress
        return  this._fb.group({
            // joining_title: ['',Validators.required],
            number_of_sheep: ['',Validators.required],
            scan_type: ['Single',Validators.required],
            booking_date: ['',Validators.required],
            // time: ['',Validators.required],
            scanner: ['',Validators.required],
            room_in: [null,Validators.required],
            ideal_days: ['',Validators.required],
            // room_out: ['',Validators.required],
            introduced_days: ['',Validators.required],
            joining_duration: ['',Validators.required],
            room_free_days: [''],
            date_to_scan: ['',Validators.required]
        })
    }

    addJoining(){
        //Add Address function 
        const control = <FormArray>this.myForm.get('joinings_array');
        control.push(this.initJoining());
           setTimeout(() => {
                jQuery('.selectpicker2').selectpicker('refresh');
            }, 150);
    }

    removeJoining(i: number){
        //remove address from list
        const control = <FormArray>this.myForm.get('joinings_array');
        control.removeAt(i);
    }

    getJoiningForm(jobForm){
        return jobForm.get('joinings_array').controls
    }


    save(model:FormGroup ,values: Joining) {
        if(model.valid){
            this.isLoading = true;
            this.bookingService.addBooking(values)
                .subscribe(
                    data => {
                        this.isLoading = false;
                        if(data.status) {
                            this.notficationService.msg('success','Success!',data.msg);
                            this.router.navigate(['/booking/list']);
                        }
                        else {
                            this.notficationService.msg('error','Error!',data.msg);
                        }
                    },
                    error => {
                        this.notficationService.msg('error','Error!',error);
                        this.isLoading = false;
                    });
        }
        else{
            this.validateAllFormFields(model);
        }
    }

    validateAllFormFields(formGroup: FormGroup) {         //{1}
        Object.keys(formGroup.controls).forEach(field => {  //{2}
            const control = formGroup.get(field);

            //IF this is FormControl   
            if (control instanceof FormControl) {             //{4}
                control.markAsTouched({ onlySelf: true });
            }

            //IF this is FormArray
            else if (control instanceof FormArray) { 
                Object.keys(control.controls).forEach(field=>{
                    this.validateAllFormFields(control.controls[field]);            //{6}
                })
            }

            //IF this is FormGroup
            else if (control instanceof FormGroup) {        //{5}
                this.validateAllFormFields(control);            //{6}
            }
        });
    }
}
