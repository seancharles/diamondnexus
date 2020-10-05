from __future__ import print_function
from boto3.session import Session

import traceback
from dirsync import sync

def lambda_handler(event, context):
  print('Loading function')
  source = './'
  dest1 = '/mnt/magento'
  try:
    sync(source, dest1, 'sync')
  except Exception as e:
    # If any other exceptions which we didn't expect are raised
    # then fail the job and log the exception message.
    print('Function failed due to exception.') 
    print(e)
    traceback.print_exc()
  print('Function complete.')   
  return "complete."
