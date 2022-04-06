<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Validator;
use App\Models\Cdr;


class CDRController extends BaseController
{
    public function uploadFile(Request $request)
    {
        
        if (empty($request->files) || !$request->hasFile('file')) {
            return $this->sendError('Missing file', ['error'=>'Missing file'],400);
        }
          
            $file = $request->file('file');
            // File Details 
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $tempPath = $file->getRealPath();
            $fileSize = $file->getSize();
            $mimeType = $file->getMimeType();
        
            // Valid File Extensions
            $valid_extension = ["csv"];
        
            // 2MB in Bytes
            $maxFileSize = 2097152; 
        
            // Check file extension
            if(!in_array(strtolower($extension),$valid_extension)){
                return $this->sendError('File is too large', ['error'=>'File is too large'],400);
            }

              // Check file size
              if($fileSize > $maxFileSize){
                return $this->sendError('Invalid File Extension', ['error'=>'Invalid File Extension'],400);
              }
        
                // File upload location
                $location = 'uploads';
        
                // Upload file
                $file->move($location,$filename);
        
                // Import CSV to Database
                $filepath = public_path($location."/".$filename);
        
                // Reading file
                $file = fopen($filepath,"r");
        
                $importData_arr = array();
                $i = 0;
        
                while (($filedata = fgetcsv($file, 1000, ",")) !== FALSE) {
                   $num = count($filedata );
                   
                   for ($c=0; $c < $num; $c++) {
                      $importData_arr[$i][] = $filedata [$c];
                   }
                   $i++;
                }
                fclose($file);

                $csvData = [];
                // Insert to MySQL database
                foreach($importData_arr as $importData){
        
                  $insertData = [
                     "customer_id"=>$importData[0],
                     "date_time"=>$importData[1],
                     "num_of_calls"=>$importData[2],
                     "did"=>$importData[3],
                     "ip_address"=>$importData[4]
                  ];
                  $cdr = Cdr::create($insertData);

                   if(!$cdr){
                       return $this->sendError('Failed to Create cdrs', [],500);    
                   }
                }
        
                return $this->sendResponse($csvData, 'file imported with success');
        } 
        
      
}

    