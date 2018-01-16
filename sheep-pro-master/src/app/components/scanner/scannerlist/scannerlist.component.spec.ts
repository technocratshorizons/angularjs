import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ScannerlistComponent } from './scannerlist.component';

describe('ScannerlistComponent', () => {
  let component: ScannerlistComponent;
  let fixture: ComponentFixture<ScannerlistComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ScannerlistComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ScannerlistComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
