using Agora.DAL.Data;
using Agora.Models;
using MySqlX.XDevAPI.Relational;

namespace Agora.BLL
{
    public interface ITemplateManager
    {
        public Task<List<Template>> GetTemplates();
        public Task SetTemplate(Template template);
        public Task RemoveTemplate(Guid idTemplate);

        public Task<List<TemplateField>> GetFields(Guid idTemplate);
        public Task SetFields(TemplateField[] fields);
        public Task RemoveField(Guid IdField);

        public Task<List<TemplateAllowedChildren>> GetChildrens(Guid idTemplate);
        public Task<List<TemplateAllowedChildren>> GetChildrenByIdNode(Guid idNode);
        
        public Task SetChildrens(TemplateAllowedChildren[] childrens);
        public Task RemoveChildren(Guid idTemplateParent, Guid idTemplate);
    }

    public class TemplateManager : ITemplateManager
    {
        private readonly ITemplateData _templateData;
        private readonly ITemplateFieldData _templateFieldData;
        private readonly ITemplateAllowedChildrenData _templateAllowedChildrenData;
        public TemplateManager(ITemplateData templateData, ITemplateFieldData templateFieldData, ITemplateAllowedChildrenData templateAllowedChildrenData)
        {
            _templateData = templateData;
            _templateFieldData = templateFieldData;
            _templateAllowedChildrenData = templateAllowedChildrenData;
        }

        #region Template
        public async Task<List<Template>> GetTemplates()
        {
            return await _templateData.GetTemplatesAsync();
        }

        public async Task SetTemplate(Template template)
        {
            if (template.IdTemplate == null)
            {
                await _templateData.InsertTemplateAsync(template);
            }
            else
            {
                await _templateData.UpdateTemplateAsync(template);
            }
        }

        public async Task RemoveTemplate(Guid idTemplate)
        {
            await _templateFieldData.DeleteFieldByIdTemplateAsync(idTemplate);
            await _templateAllowedChildrenData.DeleteChildrenByIdTemplateAsync(idTemplate);
            await _templateData.DeleteTemplateAsync(idTemplate);
        }
        #endregion
        #region Field
        public async Task<List<TemplateField>> GetFields(Guid idTemplate)
        {
            return await _templateFieldData.GetFieldsAsync(idTemplate);
        }
        public async Task SetFields(TemplateField[] fields)
        {
            TemplateField[] insert = fields.Where(w => w.IdField == null).ToArray();
            TemplateField[] update = fields.Where(w => w.IdField != null).ToArray();
            if (insert.Count() > 0)
            {
                await _templateFieldData.InsertFieldAsync(insert);
            }
            if (update.Count() > 0)
            {
                await _templateFieldData.UpdateFieldAsync(update);
            }
        }

        public async Task RemoveField(Guid IdField)
        {
            await _templateFieldData.DeleteFieldAsync(IdField);
        }
        #endregion

        #region Children
        public async Task<List<TemplateAllowedChildren>> GetChildrens(Guid idTemplate)
        {
            return await _templateAllowedChildrenData.GetChildrensAsync(idTemplate);
        }
        public async Task<List<TemplateAllowedChildren>> GetChildrenByIdNode(Guid idNode)
        {
            return await _templateAllowedChildrenData.GetChildrenByIdNodeAsync(idNode);
        }
        public async Task SetChildrens(TemplateAllowedChildren[] childrens)
        {
            Guid[]parents = childrens.Select(s => s.IdTemplateParent).ToArray<Guid>();
            List<TemplateAllowedChildren> myChildren = await _templateAllowedChildrenData.GetChildrensAsync(parents);
            List<TemplateAllowedChildren> insert = new List<TemplateAllowedChildren>();
            if (myChildren.Count() > 0)
            {
                List<TemplateAllowedChildren> update = new List<TemplateAllowedChildren>();
                childrens.ToList().ForEach((child) =>
                {
                    if (myChildren.Any(a => a.IdTemplate == child.IdTemplate && a.IdTemplateParent == child.IdTemplateParent))
                    {
                        update.Add(child);
                    }
                    else
                    {
                        insert.Add(child);
                    }
                });
                if (update.Count() > 0)
                {
                    await _templateAllowedChildrenData.UpdateChildrenAsync(update.ToArray());
                }
            }else{
                insert = childrens.ToList();   
            }
            if (insert.Count() > 0)
            {
                await _templateAllowedChildrenData.InsertChildrenAsync(insert.ToArray());
            }
            
        }
        public async Task RemoveChildren(Guid idTemplateParent, Guid idTemplate)
        {
            await _templateAllowedChildrenData.DeleteChildrenAsync(idTemplateParent, idTemplate);
        }

        #endregion
    }
}