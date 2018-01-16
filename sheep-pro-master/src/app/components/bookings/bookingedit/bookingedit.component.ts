import { Component, OnInit } from '@angular/core';
import { UserService, NotficationService, BookingService } from '../../../_services/index';
import { Validators, FormGroup, FormArray, FormBuilder, FormControl } from '@angular/forms';
import { Joining } from '../../../joining.interface';
import { Router, ActivatedRoute, ParamMap } from '@angular/router';    
import { BsDatepickerConfig } from 'ngx-bootstrap/datepicker';
declare var jQuery: any;
@Component({
  selector: 'app-bookingedit',
  templateUrl: './bookingedit.component.html',
  styleUrls: ['./bookingedit.component.css']
})
export class BookingeditComponent implements OnInit {
    isLoading: boolean = false;
    clients:object;
    scanners:object;
    id;
    bsRangeValue: any = [new Date(), new Date()];
    bsRangesValue: any = [new Date(), new Date()];
    bsConfig = Object.assign({}, { containerClass: 'theme-blue' , dateInputFormat: 'DD-MM-YYYY', showWeekNumbers:false});
    public current:any = {};
    public myForm: FormGroup; 
    constructor(private userService: UserService,
        private notficationService: NotficationService,
        private _fb: FormBuilder,
        private bookingService: BookingService,
        private router :Router,
        private route: ActivatedRoute,
        ) { }

    ngOnInit() {
        this.initForm();
        var my_form = this.myForm;
        //Get Clients Here
        // jQuery('.selectpicker2').selectpicker();
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
               }, 500);
            }
        },
        error => {
            this.notficationService.msg('error','Error!',error);
        });
        //End here

        //Get Current Booking Info
        this.route.params.subscribe(params => {
            this.id = Number.parseInt(params['id'])
            this.bookingService.currentBooking({'id':this.id}).subscribe(
            data => {
                if(data.status) {
                    this.current = data.data;
                    this.setValue(this.current);
                }else{
                   this.router.navigate(['/booking/list']); 
                }
            },
            error => {
                this.notficationService.msg('error','Error!',error);
            });
        });
    }


    ChangeEvent(argument) {
    
    }


    initForm(){
        //Set Form Builder
        this.myForm =  this._fb.group({
            join_id:[''],
            client_details: ['',Validators.required],
            unavailable_dates: [''],
            client_confirmation_email: [''],
            client_confirmation_phone: [''],
            notes: [''],
            number_of_sheep: ['',Validators.required],
            scan_type: ['',Validators.required],
            booking_date: ['',Validators.required],
            // time: ['',Validators.required],
            scanner: ['',Validators.required],
            room_in: [[new Date(), new Date()],Validators.required],
            ideal_days: [],
            // room_out: ['',Validators.required],
            introduced_days: ['0',Validators.required],
            joining_duration: ['0',Validators.required],
            room_free_days: ['0'],
            date_to_scan: [null,Validators.required],
            status:[null,Validators.required]
        })
        //End here
    }
    setValue(obj){
        this.myForm.controls.number_of_sheep.setValue(obj.number_of_sheep);
        this.myForm.controls.join_id.setValue(obj.join_id);
        this.myForm.controls.client_details.setValue(obj.client_id);
        this.myForm.controls.unavailable_dates.setValue(obj.unavailable_dates);
        this.myForm.controls.scan_type.setValue(obj.scan_type);
        // this.myForm.controls.booking_date.setValue(obj.booking_date);
        if(obj.client_confirmation_email=='1'){
            this.myForm.controls.client_confirmation_email.setValue(true);
        }else{
            this.myForm.controls.client_confirmation_email.setValue(false);
        }
        if(obj.client_confirmation_sms=='1'){
            this.myForm.controls.client_confirmation_phone.setValue(true);
        }else{
            this.myForm.controls.client_confirmation_phone.setValue(false);
        }
        // this.myForm.controls.client_confirmation_phone.setValue(obj.client_confirmation_sms);
        this.myForm.controls.notes.setValue(obj.notes);
        this.myForm.controls.scanner.setValue(obj.scanner);
         setTimeout(() => {
                    jQuery('.selectpicker2').selectpicker('refresh');
                    jQuery('.scanner').selectpicker('refresh');
               }, 250);

        
        this.myForm.controls.room_in.setValue([new Date(obj.room_in),new Date(obj.room_out)]);
        this.myForm.controls.booking_date.setValue([new Date(obj.booking_date),new Date(obj.booking_date_end)]);
        this.myForm.controls.date_to_scan.setValue(obj.date_to_scan);
        this.myForm.controls.status.setValue(obj.status);
    }
    save(model: any) {
        if(model.valid){
            this.isLoading = true;
            this.bookingService.editBooking(model.value)
                .subscribe(
                    data => {
                        this.isLoading = false;
                        if(data.status) {
                            this.notficationService.msg('success','Success!',data.msg);
                            // this.router.navigate(['/booking/list']);
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
