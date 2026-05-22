using Agora.DAL.Data;
using Agora.Models;
namespace Agora.BLL
{
    public interface IMasterManager
    {
        public Task<List<Master>> GetMasters();
        public Task SetMaster(Master master);
        public Task RemoveMaster(Guid idMaster);
        //Option Sector
        public Task<List<MasterOption>> GetOptions(Guid idMaster);
        public Task SetOptions(MasterOption[] options);
        public Task RemoveOptions(Guid idOption);
    }

    public class MasterManager : IMasterManager
    {
        private readonly IMasterData _masterData;
        public MasterManager(IMasterData masterData)
        {
            _masterData = masterData;
        }

        #region Master
        public async Task<List<Master>> GetMasters()
        {
            return await _masterData.GetMastersAsync();
        }

        public async Task SetMaster(Master master)
        {
            if (master.IdMaster == null)
            {
                await _masterData.InsertMasterAsync(master);
            }
            else
            {
                await _masterData.UpdateMasterAsync(master);
            }
        }

        public async Task RemoveMaster(Guid idMaster)
        {
            await _masterData.DeleteOptionsByIdMasterAsync(idMaster);
            await _masterData.DeleteMasterAsync(idMaster);
        }
        #endregion

        #region Option
        public async Task<List<MasterOption>> GetOptions(Guid idMaster)
        {
            return await _masterData.GetMasterOptionAsync(idMaster);
        }

        public async Task SetOptions(MasterOption[] options)
        {
            MasterOption[] insert = options.Where(w => w.IdOption == null).ToArray();
            MasterOption[] update = options.Where(w => w.IdOption != null).ToArray();
            if (insert.Count() > 0)
            {
                await _masterData.InsertOptionAsync(insert);
            }
            if (update.Count() > 0)
            {
                await _masterData.UpdateOptionAsync(update);
            }
        }

        public async Task RemoveOptions(Guid idOption)
        {
            await _masterData.DeleteOptionAsync(idOption);
        }
        #endregion
    }
}