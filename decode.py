import glob
from os import path
import stepic
from datetime import datetime
import Image


# Function to decode the stego message from image
def  decode(filepath):
	img = Image.open(filepath)
	if(img.mode!='L'):
		message = stepic.decode(img)
		return message
	return "  "

#Function to check if a message is a valid msg
def isvalidformat(msg):
	if ((msg[0]=='0' or msg[0]=='1')) and (msg[1] >= '0' and msg[1] <= '9') :
		return True
	else:
		return False

#Function to get details from message
def getdetails(msg):
	pwlength = int(msg[0:2])
	login = []
	login.append(msg[2:pwlength+2])
	login.append(msg[pwlength+2:-1])
	[l1,l2] =  login[1].split('@',1)
	login[1] = l1
	login.append(l2)
	return login

#Main Function
decode_dir = path.expanduser("images/")
for infile in glob.glob(decode_dir+"*.jpg"):
	msg = decode(infile)
	if isvalidformat(msg):
		print "\n-->DECODE RUN :", infile
		login = getdetails(msg)
		print "Username :", login[1], "\nPassword :", login[0], "\nHostname:",login[2]

f = open('updated.txt','w+')			# Change file to /etc/profile
f.write(datetime.now().strftime("%Y-%m-%d %H:%M:%S"))
f.close()


