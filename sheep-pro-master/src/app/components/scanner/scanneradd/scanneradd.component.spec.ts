import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ScanneraddComponent } from './scanneradd.component';

describe('ScanneraddComponent', () => {
  let component: ScanneraddComponent;
  let fixture: ComponentFixture<ScanneraddComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ScanneraddComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ScanneraddComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
